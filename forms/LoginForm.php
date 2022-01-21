<?php

namespace steroids\auth\forms;

use steroids\auth\AuthModule;
use steroids\auth\enums\AuthAttributeTypeEnum;
use steroids\auth\forms\meta\LoginFormMeta;
use steroids\auth\models\AuthConfirm;
use steroids\auth\UserInterface;
use steroids\auth\validators\CaptchaValidator;
use steroids\auth\validators\LoginValidator;
use steroids\core\validators\PhoneValidator;
use steroids\notifier\exceptions\InvalidPhoneNumberException;
use yii\helpers\ArrayHelper;

class LoginForm extends LoginFormMeta
{
    /**
     * @var UserInterface
     */
    public $user;

    /**
     * @var string
     */
    public $accessToken;

    /**
     * @var string
     */
    public $token;

    public ?AuthConfirm $confirm = null;

    public function fields()
    {
        return [
            'accessToken',
            'confirm',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        $module = AuthModule::getInstance();
        $rules = [];

        if ($module->isPasswordAvailable) {
            $rules = [
                ['login', 'filter', 'filter' => fn($value) => mb_strtolower(trim($value))],
                ['password', 'required'],
            ];
        }

        //Add captcha
        $rules[] = ['token', CaptchaValidator::class];

        if (in_array(AuthAttributeTypeEnum::EMAIL, $module->loginAvailableAttributes)
            && strpos($this->login, '@') !== false) {
            $rules[] = ['login', 'email'];
        } elseif (in_array(AuthAttributeTypeEnum::PHONE, $module->loginAvailableAttributes)
            && preg_match('/^\+?[0-9]+$/', trim($this->login))) {
            $rules[] = ['login', PhoneValidator::class];
        } elseif (in_array(AuthAttributeTypeEnum::LOGIN, $module->loginAvailableAttributes)) {
            $rules[] = ['login', LoginValidator::class];
        }

        // Find user and check password (if enable)
        $rules = [
            ...$rules,
            [
                $module->isPasswordAvailable ? 'password' : 'login',
                function ($attribute) use ($module) {
                    /** @var UserInterface $userClass */
                    $userClass = AuthModule::getInstance()->userClass;

                    // Find user by email/phone/login
                    $attributes = array_map(
                        fn($attribute) => $module->getUserAttributeName($attribute),
                        $module->loginAvailableAttributes
                    );
                    $this->user = $userClass::findBy($this->login, $attributes);

                    if (!$this->user || ($module->isPasswordAvailable && !$this->user->validatePassword($this->password))) {
                        $this->password = null;
                        $this->addError($attribute, \Yii::t('steroids', 'Неверный логин или пароль'));
                    }
                },
            ],
        ];

        return [
            ...parent::rules(),
            ...$rules,

            // Check confirms
            ['login', function ($attribute) use ($module) {
                // we should check if a user is confirmed only when authorization is performed by password
                // because if not confirmation code should be sent at every user attempt to login
                if ($this->user && !$this->hasErrors() && $module->isPasswordAvailable && $module->isConfirmRequired) {
                    $isConfirmed = AuthConfirm::find()
                        ->where([
                            'userId' => $this->user->getId(),
                            'isConfirmed' => true,
                            'type' => $module->registrationMainAttribute,
                        ])
                        ->exists();
                    if (!$isConfirmed) {
                        $messages = [
                            AuthAttributeTypeEnum::EMAIL => \Yii::t('steroids', 'Email не подтвержден. Проверьте почту или восстановите пароль'),
                            AuthAttributeTypeEnum::PHONE => \Yii::t('steroids', 'Телефон не подтвержден. Проверьте телефон или восстановите пароль'),
                        ];
                        $message = ArrayHelper::getValue($messages, $module->registrationMainAttribute, \Yii::t('steroids', 'Логин не подтвержден.'));
                        $this->addError($attribute, $message);
                    }
                }
            }],
            //check isBanned
            ['login', function ($attribute) {
                if ($this->user && $this->user->getIsBanned()) {
                    $this->addError($attribute, \Yii::t('steroids', 'Аккаунт заблокирован, обратитесь в техническую поддержку'));
                }
            }]
        ];
    }

    /**
     * @throws \Exception
     */
    public function login()
    {
        if ($this->validate()) {
            if (AuthModule::getInstance()->isPasswordAvailable) {
                // Login now, because password checked
                \Yii::$app->user->login($this->user);
                $this->accessToken = \Yii::$app->user->accessToken;
            } else {
                // Send confirm code
                $module = AuthModule::getInstance();
                try {
                    $this->confirm = $module->confirm($this->user, $module->registrationMainAttribute);
                } catch (InvalidPhoneNumberException $e) {
                    $this->addError($module->registrationMainAttribute, InvalidPhoneNumberException::getDefaultMessage());
                }
            }
        }
    }
}
