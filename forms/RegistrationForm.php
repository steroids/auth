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

    /**
     * @inheritDoc
     */
    public function rules()
    {
        /** @var UserInterface $userClass */
        $userClass = Yii::$app->user->identityClass;

        $rules = parent::rules();
        $module = AuthModule::getInstance();

        switch ($module->registrationMainAttribute) {
            case AuthModule::ATTRIBUTE_EMAIL:
                // Email
                $rules = [
                    ...$rules,
                    ['email', 'filter', 'filter' => fn($value) => mb_strtolower(trim($value))],
                    ['email', 'unique', 'targetClass' => $userClass, 'targetAttribute' => $module->emailAttribute],
                ];
                break;

            case AuthModule::ATTRIBUTE_PHONE:
                // Phone
                $rules = [
                    ...$rules,
                    ['phone', PhoneValidator::class],
                    ['phone', 'unique', 'targetClass' => $userClass, 'targetAttribute' => $module->phoneAttribute],
                ];
                break;

            case AuthModule::ATTRIBUTE_LOGIN:
                // Login
                $rules = [
                    ...$rules,
                    ['login', 'filter', 'filter' => fn($value) => mb_strtolower(trim($value))],
                    ['login', LoginValidator::class],
                    ['login', 'unique', 'targetClass' => $userClass, 'targetAttribute' => $module->loginAttribute],
                ];
                break;
        }

        // Password
        if ($module->isPasswordAvailable) {
            $rules = [
                ...$rules,
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
        /** @var UserInterface $userClass */
        $userClass = Yii::$app->user->identityClass;

        if ($this->validate()) {
            $module = AuthModule::getInstance();

            // Create user
            $this->user = new $userClass();

            // Set email/phone/login
            switch ($module->registrationMainAttribute) {
                case AuthModule::ATTRIBUTE_EMAIL:
                    $this->user->setAttribute($module->emailAttribute, $this->email);
                    break;

                case AuthModule::ATTRIBUTE_PHONE:
                    $this->user->setAttribute($module->phoneAttribute, $this->phone);
                    break;

                case AuthModule::ATTRIBUTE_LOGIN:
                    $this->user->setAttribute($module->loginAttribute, $this->login);
                    break;
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
    public function onUnsafeAttribute($name, $value)
    {
        if (in_array($name, AuthModule::getInstance()->registrationCustomAttributes)) {
            $this->custom[$name] = $value;
        } else {
            parent::onUnsafeAttribute($name, $value);
        }
    }
}
