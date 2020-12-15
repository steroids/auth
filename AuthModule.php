<?php

namespace steroids\auth;

use app\user\models\User;
use steroids\auth\authenticators\GoogleAuthenticator;
use steroids\auth\components\captcha\CaptchaComponentInterface;
use steroids\auth\components\captcha\ReCaptchaV3;
use steroids\auth\enums\AuthAttributeTypeEnum;
use InvalidArgumentException;
use steroids\auth\exceptions\ConfirmCodeAlreadySentException;
use steroids\auth\forms\ConfirmForm;
use steroids\auth\forms\LoginForm;
use steroids\auth\forms\RecoveryPasswordConfirmForm;
use steroids\auth\forms\RecoveryPasswordForm;
use steroids\auth\forms\RegistrationForm;
use steroids\auth\forms\SocialEmailConfirmForm;
use steroids\auth\forms\SocialEmailForm;
use steroids\auth\forms\ProviderLoginForm;
use steroids\auth\models\Auth2FaValidation;
use steroids\auth\models\AuthConfirm;
use steroids\auth\models\AuthLogin;
use steroids\auth\models\AuthSocial;
use steroids\auth\models\NotifierAuthenticator;
use steroids\auth\providers\FacebookAuthProvider;
use steroids\auth\providers\GoogleAuthProvider;
use steroids\auth\providers\SteamAuthProvider;
use steroids\auth\providers\VkAuthProvider;
use steroids\core\base\Model;
use steroids\core\base\Module;
use steroids\core\traits\ModuleProvidersTrait;
use steroids\core\exceptions\ModelSaveException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class AuthModule extends Module
{
    use ModuleProvidersTrait;

    const TWOFA_CODE_IS_SEND = '2fa code has been send';
    const TWOFA_CODE_SUCCESS = 'Authentication success';
    const TWOFA_CODE_FAILED = 'Authentication failed';

    /**
     * Email attribute in User model
     */
    public string $emailAttribute = 'email';

    /**
     * Phone attribute in User model
     */
    public string $phoneAttribute = 'phone';

    /**
     * Login attribute in User model
     */
    public string $loginAttribute = 'login';

    /**
     * Password hash attribute in User model
     */
    public string $passwordHashAttribute = 'passwordHash';

    /**
     * What attributes user can be used as a login
     */
    public array $loginAvailableAttributes = [
        AuthAttributeTypeEnum::EMAIL,
    ];

    /**
     * Main required attribute as login
     */
    public string $registrationMainAttribute = AuthAttributeTypeEnum::EMAIL;

    /**
     * Additional attributes for registration form. Will be validated by User model
     */
    public array $registrationCustomAttributes = [];

    /**
     * Required attributes (default or custom)
     */
    public array $registrationRequiredAttributes = [];

    /**
     * Set false for auth only by email/phone + code
     */
    public bool $isPasswordAvailable = true;

    /**
     * Generated code length (on confirm email or phone)
     */
    public int $confirmCodeLength = 6;

    /**
     * Maximum mins for confirm code
     */
    public int $confirmExpireMins = 60;

    /**
     * User class name which implement UserInterface
     * @var string
     */
    public string $userClass = '';

    /**
     * Should captcha be used in auth forms
     * @var bool
     */
    public bool $isCaptchaEnable = false;

    public string $auth2FaValidationLiveTime = '-2 minutes';

    /**
     * @var CaptchaComponentInterface|array|null
     */
    public $captcha = [];

    public array $providersClasses = [];

    public bool $debugSkipConfirmCodeCheck = false;

    /**
     * @param int|null $length
     * @param string $attributeType
     * @return int
     * @throws \Exception
     */
    public static function generateCode($length = 6, $attributeType = null)
    {
        $length = max(1, $length);
        $number = random_int(pow(10, $length - 1), pow(10, $length) - 1);
        return str_pad($number, $length, '0', STR_PAD_LEFT);
    }

    /**
     * @throws InvalidConfigException
     * @throws \ReflectionException
     */
    public function init()
    {
        parent::init();

        if (!$this->userClass) {
            throw new InvalidConfigException('Please set "userClass" property in AuthModule configuration');
        }

        $this->classesMap = array_merge([
            'steroids\auth\models\AuthConfirm' => AuthConfirm::class,
            'steroids\auth\models\AuthLogin' => AuthLogin::class,
            'steroids\auth\models\AuthSocial' => AuthSocial::class,
            'steroids\auth\forms\ConfirmForm' => ConfirmForm::class,
            'steroids\auth\forms\LoginForm' => LoginForm::class,
            'steroids\auth\forms\RecoveryPasswordConfirmForm' => RecoveryPasswordConfirmForm::class,
            'steroids\auth\forms\RecoveryPasswordForm' => RecoveryPasswordForm::class,
            'steroids\auth\forms\RegistrationForm' => RegistrationForm::class,
            'steroids\auth\forms\SocialEmailConfirmForm' => SocialEmailConfirmForm::class,
            'steroids\auth\forms\SocialEmailForm' => SocialEmailForm::class,
            'steroids\auth\forms\ProviderLoginForm' => ProviderLoginForm::class,
            'steroids\auth\AuthProfile' => AuthProfile::class,
        ], $this->classesMap);

        $this->providersClasses = array_merge([
            'facebook' => FacebookAuthProvider::class,
            'google' => GoogleAuthProvider::class,
            'steam' => SteamAuthProvider::class,
            'vk' => VkAuthProvider::class,
        ], $this->providersClasses);

        if ($this->isCaptchaEnable && is_array($this->captcha)) {
            $this->captcha = \Yii::createObject(array_merge(
                ['class' => ReCaptchaV3::class],
                $this->captcha
            ));
        }
    }

    /**
     * @param string $attribute
     * @return mixed|null
     */
    public function getUserAttributeName($attribute)
    {
        $map = [
            AuthAttributeTypeEnum::EMAIL => $this->emailAttribute,
            AuthAttributeTypeEnum::PHONE => $this->phoneAttribute,
            AuthAttributeTypeEnum::LOGIN => $this->loginAttribute,
        ];
        return ArrayHelper::getValue($map, $attribute);
    }

    /**
     * @param UserInterface|Model $user
     * @param string $attributeType one of AuthAttributeTypeEnum::EMAIL, AuthAttributeTypeEnum::PHONE
     * @param bool $is2fa
     * @return null|AuthConfirm
     * @throws ModelSaveException|InvalidArgumentException|ConfirmCodeAlreadySentException
     */

    public function confirm($user, $attributeType = null, $is2fa = false)
    {
        if (!$attributeType) {
            $attributeType = $this->registrationMainAttribute;
        }

        if (!in_array($attributeType, self::getNotifierTypes())) {
            return null;
        }

        $attribute = $attributeType === AuthAttributeTypeEnum::PHONE
            ? $this->phoneAttribute
            : $this->emailAttribute;

        $authConfirmAttributes = [
            'type' => $attributeType,
            'value' => $user->getAttribute($attribute),
            'userId' => $user->getId(),
            'is2Fa' => $is2fa,
        ];

        $confirmHasBeenAlreadySend = AuthConfirm::find()
            ->where($authConfirmAttributes)
            ->andWhere(['>=', 'expireTime', date('Y-m-d H:i:s')])
            ->andWhere(['isConfirmed' => false])
            ->one();

        if ($confirmHasBeenAlreadySend) {
            $diffInSeconds = strtotime($confirmHasBeenAlreadySend->expireTime) - strtotime("now");
            $message = 'Код уже был отправлен, повторная отправка возможна через ' . $diffInSeconds;
            throw new ConfirmCodeAlreadySentException($message);
        }

        $authConfirmAttributes['code'] = static::generateCode($this->confirmCodeLength, $attributeType);

        // Create confirm
        $model = AuthConfirm::instantiate(array_merge($authConfirmAttributes, [
            'code' => static::generateCode($this->confirmCodeLength, $attributeType)
        ]));

        $model->saveOrPanic();

        // Send mail
        $user->sendNotify(AuthConfirm::TEMPLATE_NAME, [
            'confirm' => $model,
        ]);

        return $model;
    }

    /**
     * @param string $prevConfirmUid
     * @return AuthConfirm|null
     * @throws ModelSaveException
     * @throws \yii\web\NotFoundHttpException
     */
    public function resendConfirm(string $prevConfirmUid)
    {
        $prevConfirm = AuthConfirm::findOrPanic(['uid' => $prevConfirmUid]);
        return $this->confirm($prevConfirm->user, $prevConfirm->type);
    }

    public function coreComponents()
    {
        return parent::coreComponents(); // TODO: Change the autogenerated stub
    }

    /**
     * @param User $user
     * @param string $login user email or phone for authenticate
     * @param string $code verification code
     * @return string one of self::TWOFA_CODE_IS_SEND, self::TWOFA_CODE_SUCCESS, self::TWOFA_CODE_FAILED
     * @throws ModelSaveException|\yii\base\Exception
     */
    public function authenticate2FA(User $user, string $login, string $code)
    {
        $authenticator = !$login
            ? new GoogleAuthenticator()
            : new NotifierAuthenticator();


        //checking if user recently used 2Fa
        $authValidate = Auth2FaValidation::find()
            ->where([
                'userId' => $user->id,
                'authenticatorType' => $authenticator->type,
            ])
            ->andWhere(['>=', 'createTime', date("Y-m-d H:i", strtotime($this->auth2FaValidationLiveTime))])
            ->one();

        if ($authValidate) {
            return self::TWOFA_CODE_IS_SEND;
        }

        if ($authenticator instanceof NotifierAuthenticator && !$authValidate) {
            $authenticator->sendCode($login);

            return self::TWOFA_CODE_IS_SEND;
        }

        return $authenticator->validateCode($code, $login)
            ? self::TWOFA_CODE_SUCCESS
            : self::TWOFA_CODE_FAILED;
    }

    /**
     * @param string $login value of the user's login attributes
     * @return string one of AuthAttributeTypeEnum
     */
    public static function getNotifierAttributeTypeFromLogin(string $login): string
    {
        return strpos($login, '@') !== false
            ? AuthAttributeTypeEnum::EMAIL
            : AuthAttributeTypeEnum::PHONE;
    }

    public static function getNotifierTypes()
    {
        return [
            AuthAttributeTypeEnum::EMAIL,
            AuthAttributeTypeEnum::PHONE
        ];
    }
}
