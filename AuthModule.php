<?php

namespace steroids\auth;

use steroids\auth\components\captcha\CaptchaComponentInterface;
use steroids\auth\components\captcha\ReCaptchaV3;
use ReflectionClass;
use steroids\auth\forms\ConfirmForm;
use steroids\auth\forms\LoginForm;
use steroids\auth\forms\RecoveryPasswordConfirmForm;
use steroids\auth\forms\RecoveryPasswordForm;
use steroids\auth\forms\RegistrationForm;
use steroids\auth\forms\SocialEmailConfirmForm;
use steroids\auth\forms\SocialEmailForm;
use steroids\auth\forms\ProviderLoginForm;
use steroids\auth\models\AuthConfirm;
use steroids\auth\models\AuthLogin;
use steroids\auth\models\AuthSocial;
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

    const ATTRIBUTE_EMAIL = 'email';
    const ATTRIBUTE_PHONE = 'phone';
    const ATTRIBUTE_LOGIN = 'login';

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
        self::ATTRIBUTE_EMAIL,
    ];

    /**
     * Main required attribute as login
     */
    public string $registrationMainAttribute = self::ATTRIBUTE_EMAIL;

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

    /**
     * @var CaptchaComponentInterface|array|null
     */
    public ?array $captcha = [];

    /**
     * @var array|null
     */
    public ?array $authentificators = [];

    public array $providersClasses = [
        'facebook' => FacebookAuthProvider::class,
        'google' => GoogleAuthProvider::class,
        'steam' => SteamAuthProvider::class,
        'vk' => VkAuthProvider::class,
    ];

    public array $classesMap = [
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
    ];

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

        $this->captcha = array_merge(
            ['class' => ReCaptchaV3::class],
            $this->captcha
        );
    }

    /**
     * @param string $attribute
     * @return mixed|null
     */
    public function getUserAttributeName($attribute)
    {
        $map = [
            static::ATTRIBUTE_EMAIL => $this->emailAttribute,
            static::ATTRIBUTE_PHONE => $this->phoneAttribute,
            static::ATTRIBUTE_LOGIN => $this->loginAttribute,
        ];
        return ArrayHelper::getValue($map, $attribute);
    }

    /**
     * @param UserInterface|Model $user
     * @param string $attributeType
     * @return null|AuthConfirm
     * @throws ModelSaveException
     */
    public function confirm($user, $attributeType = null)
    {
        if (!$attributeType) {
            $attributeType = $this->registrationMainAttribute;
        }
        if (!in_array($attributeType, [AuthModule::ATTRIBUTE_EMAIL, AuthModule::ATTRIBUTE_PHONE])) {
            return null;
        }

        $attribute = $attributeType === AuthModule::ATTRIBUTE_PHONE
            ? $this->phoneAttribute
            : $this->emailAttribute;

        // Create confirm
        $model = AuthConfirm::instantiate([
            'type' => $attributeType,
            'value' => $user->getAttribute($attribute),
            'userId' => $user->getId(),
            'code' => static::generateCode($this->confirmCodeLength, $attributeType),
        ]);
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
}
