<?php

namespace steroids\auth;

use steroids\auth\authenticators\NotifierAuthenticator;
use Yii;
use steroids\auth\authenticators\BaseAuthenticator;
use steroids\auth\providers\BaseAuthProvider;
use steroids\auth\authenticators\GoogleAuthenticator;
use steroids\auth\components\captcha\CaptchaComponentInterface;
use steroids\auth\components\captcha\ReCaptchaV3;
use steroids\auth\enums\AuthAttributeTypeEnum;
use steroids\auth\forms\ConfirmForm;
use steroids\auth\forms\LoginForm;
use steroids\auth\forms\RecoveryPasswordConfirmForm;
use steroids\auth\forms\RecoveryPasswordForm;
use steroids\auth\forms\RegistrationForm;
use steroids\auth\forms\SocialEmailConfirmForm;
use steroids\auth\forms\SocialEmailForm;
use steroids\auth\forms\AuthProviderLoginForm;
use steroids\auth\models\AuthConfirm;
use steroids\auth\models\AuthLogin;
use steroids\auth\models\AuthSocial;
use steroids\auth\providers\FacebookAuthProvider;
use steroids\auth\providers\GoogleAuthProvider;
use steroids\auth\providers\SteamAuthProvider;
use steroids\auth\providers\VkAuthProvider;
use steroids\core\base\Model;
use steroids\core\base\Module;
use steroids\core\exceptions\ModelSaveException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class AuthModule extends Module
{
    const EVENT_CONFIRMATION_RESEND = 'confirmation_resend';

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
     * Username attribute in User model
     */
    public string $nameAttribute = 'username';

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
     * Whether user should be registered if wasn't found while attempting to login
     */
    public bool $autoRegistration = false;

    /**
     * Generated code length (on confirm email or phone)
     */
    public int $confirmCodeLength = 6;

    /**
     * Maximum mins for confirm code
     */
    public int $confirmExpireMins = 60;

    /**
     * Timeout limit in second for repeat send
     */
    public int $confirmRepeatLimitSec = 60;

    /**
     * User class name which implement UserInterface
     * @var string
     */
    public string $userClass = '';

    /**
     * Captcha component (null for disabled)
     * @var CaptchaComponentInterface|array|null
     */
    public $captcha;

    /**
     * Auth (social) providers configs
     * @var array
     */
    public array $authProviders = [];

    /**
     * Auth (social) provider default classes
     * @var array
     */
    public array $authProvidersClasses = [];

    /**
     * Two factor authenticator providers configs
     * @var array
     */
    public array $twoFactorProviders = [];

    /**
     * Two factor authenticator provider default classes
     * @var array
     */
    public array $twoFactorProvidersClasses = [];

    /**
     * Flag for enable static code "111111" in confirmations
     * @var bool
     */
    public bool $enableDebugStaticCode = false;

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
            'steroids\auth\forms\AuthProviderLoginForm' => AuthProviderLoginForm::class,
            'steroids\auth\AuthProfile' => AuthProfile::class,
        ], $this->classesMap);

        $this->authProvidersClasses = array_merge([
            'facebook' => FacebookAuthProvider::class,
            'google' => GoogleAuthProvider::class,
            'steam' => SteamAuthProvider::class,
            'vk' => VkAuthProvider::class,
        ], $this->authProvidersClasses);

        $this->twoFactorProvidersClasses = array_merge([
            'google' => GoogleAuthenticator::class,
            'notifier' => NotifierAuthenticator::class,
        ], $this->twoFactorProvidersClasses);

        if (is_array($this->captcha)) {
            $this->captcha = \Yii::createObject(array_merge(
                ['class' => ReCaptchaV3::class],
                $this->captcha
            ));
        }
    }

    /**
     * @param string $name
     * @return BaseAuthProvider
     * @throws InvalidConfigException
     */
    public function getAuthProvider(string $name)
    {
        return $this->getProvider($name, 'authProviders', 'authProvidersClasses');
    }

    /**
     * @param string $name
     * @return BaseAuthenticator
     * @throws InvalidConfigException
     */
    public function getTwoFactorProvider(string $name)
    {
        return $this->getProvider($name, 'twoFactorProviders', 'twoFactorProvidersClasses');
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
     * @param null|AuthConfirm $prevConfirm previous confirmation
     * @return null|AuthConfirm
     * @throws ModelSaveException
     * @throws Exception
     */
    public function confirm($user, $attributeType = null, $prevConfirm = null)
    {
        // Validate attribute type
        if (!$attributeType) {
            $attributeType = $this->registrationMainAttribute;
        }
        if (!in_array($attributeType, AuthAttributeTypeEnum::getNotifierTypes())) {
            throw new Exception('Wrong attribute type: ' . $attributeType);
        }

        // AuthConfirm params
        $params = [
            'userId' => $user->getId(),
            'type' => $attributeType,
            'value' => $user->getAttribute(
                $attributeType === AuthAttributeTypeEnum::PHONE
                    ? $this->phoneAttribute
                    : $this->emailAttribute
            ),
        ];

        /** @var AuthConfirm $model */
        $model = null;

        // Check already sent (on limit confirmRepeatLimitSec)
        if ($this->confirmRepeatLimitSec > 0) {
            $model = AuthConfirm::find()
                ->where($params)
                ->andWhere(['>=', 'createTime', date('Y-m-d H:i:s', strtotime('-' . $this->confirmRepeatLimitSec . ' seconds'))])
                ->andWhere(['isConfirmed' => false])
                ->one();
            if ($model) {
                // Mark reused
                $model->isReused = true;
                $model->saveOrPanic();
                return $model;
            }
        }

        // Or create new
        if (!$model) {
            $code = static::getInstance()->enableDebugStaticCode
                ? str_repeat('1', static::getInstance()->confirmCodeLength)
                : static::generateCode($this->confirmCodeLength, $attributeType);
            $model = AuthConfirm::instantiate(array_merge($params, [
                'code' => $code,
                'prevId' => $prevConfirm ? $prevConfirm->primaryKey : null,
            ]));
        }

        // Save
        $model->saveOrPanic();

        // Send sms/mail
        $user->sendNotify(AuthConfirm::TEMPLATE_NAME, [
            'confirm' => $model,
        ]);

        // Complete previous confirmation
        if ($prevConfirm && !$prevConfirm->isConfirmed) {
            $prevConfirm->expireTime = date('Y-m-d H:i:s');
            $prevConfirm->saveOrPanic();
        }

        return $model;
    }

    /**
     * @param string $name
     * @param string $itemsKey
     * @param string $classesKey
     * @return array|mixed|object|null
     * @throws InvalidConfigException
     */
    protected function getProvider(string $name, string $itemsKey, string $classesKey)
    {
        /** @var array $items */
        $items = $this->$itemsKey;

        if (!$items || !isset($items[$name])) {
            throw new Exception('Not found provider: ' . $name);
        }

        if (is_array($items[$name])) {
            $items[$name] = Yii::createObject(array_merge(
                ['class' => ArrayHelper::getValue($this->$classesKey, $name)],
                $items[$name],
                ['name' => $name]
            ));
        }

        return $items[$name];
    }
}
