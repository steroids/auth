<?php

namespace steroids\auth\forms;

use steroids\auth\models\AuthConfirm;
use steroids\core\exceptions\ModelSaveException;
use Yii;
use steroids\auth\AuthModule;
use steroids\auth\forms\meta\RegistrationFormMeta;
use steroids\auth\UserInterface;
use steroids\auth\validators\LoginValidator;
use steroids\core\base\Model;
use steroids\core\validators\PasswordValidator;
use steroids\core\validators\PhoneValidator;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\validators\RequiredValidator;

class RegistrationForm extends RegistrationFormMeta
{
    /**
     * @var array
     */
    public array $custom = [];

    /**
     * @var UserInterface|Model
     */
    public $user;

    /**
     * @var AuthConfirm
     */
    public $confirm;

    public function fields()
    {
        return [
            'user',
            'confirm' => [
                'uid',
                'type',
                'value',
                'expireTime',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $module = AuthModule::getInstance();
        $userClass = $module->userClass;

        // Email
        if ($module->registrationMainAttribute === AuthModule::ATTRIBUTE_EMAIL ||
            in_array(AuthModule::ATTRIBUTE_EMAIL, $module->loginAvailableAttributes)) {
            $rules = [
                ...$rules,
                ['email', 'filter', 'filter' => fn($value) => mb_strtolower(trim($value))],
                ['email', 'unique', 'targetClass' => $userClass, 'targetAttribute' => $module->emailAttribute],
            ];
        }

        // Phone
        if ($module->registrationMainAttribute === AuthModule::ATTRIBUTE_PHONE ||
            in_array(AuthModule::ATTRIBUTE_PHONE, $module->loginAvailableAttributes)) {
            $rules = [
                ...$rules,
                ['phone', PhoneValidator::class],
                ['phone', 'unique', 'targetClass' => $userClass, 'targetAttribute' => $module->phoneAttribute],
            ];
        }

        // Login
        if ($module->registrationMainAttribute === AuthModule::ATTRIBUTE_LOGIN ||
            in_array(AuthModule::ATTRIBUTE_LOGIN, $module->loginAvailableAttributes)) {
            $rules = [
                ...$rules,
                ['login', 'filter', 'filter' => fn($value) => mb_strtolower(trim($value))],
                ['login', LoginValidator::class],
                ['login', 'unique', 'targetClass' => $userClass, 'targetAttribute' => $module->loginAttribute],
            ];
        }

        // Password
        if ($module->isPasswordAvailable) {
            $rules = [
                ...$rules,
                [$module->registrationMainAttribute, 'required'],
                ['password', 'required'],
                ['password', PasswordValidator::class],
                ['password', 'compare', 'compareAttribute' => 'passwordAgain'],
            ];
        }

        return $rules;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function register()
    {
        $module = AuthModule::getInstance();
        $userClass = $module->userClass;

        if ($this->validate()) {
            // Create user
            $this->user = new $userClass();

            // Set email/phone/login
            if ($this->email) {
                $this->user->setAttribute($module->emailAttribute, $this->email);
            }
            if ($this->phone) {
                $this->user->setAttribute($module->phoneAttribute, $this->phone);
            }
            if ($this->login) {
                $this->user->setAttribute($module->loginAttribute, $this->login);
            }

            // Set password
            if ($module->isPasswordAvailable) {
                $this->user->setAttribute($module->passwordHashAttribute, Yii::$app->security->generatePasswordHash($this->password));
            }

            // Set custom attributes
            if (!empty($module->registrationCustomAttributes)) {
                $this->user->setAttributes($this->custom);
            }

            // Validate by user model
            if (!$this->user->validate()) {
                $this->addErrors($this->user->getErrors());
                return false;
            }

            // Save with transaction
            $transaction = static::getDb()->beginTransaction();
            try {
                // Save user
                if ($this->user instanceof Model) {
                    $this->user->saveOrPanic();
                } elseif (!$this->user->save()) {
                    throw new ModelSaveException($this->user);
                }

                // Confirm email
                $this->confirm = $module->confirm($this->user, $module->registrationMainAttribute);

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }

            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function beforeValidate()
    {
        // Check custom required attributes
        $validator = new RequiredValidator();
        foreach (AuthModule::getInstance()->registrationRequiredAttributes as $attribute) {
            $value = $this->isAttributeSafe($attribute)
                ? $this->$attribute
                : ArrayHelper::getValue($this->custom, $attribute);
            if ($validator->isEmpty($value)) {
                $this->addError($attribute, $validator->message);
            }
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritDoc
     */
    public function onUnsafeAttribute($name, $value)
    {
        if (in_array($name, AuthModule::getInstance()->registrationCustomAttributes)) {
            $this->custom[$name] = $value;
        } else {
            parent::onUnsafeAttribute($name, $value);
        }
    }
}
