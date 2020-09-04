<?php

namespace steroids\auth\forms;

use steroids\auth\AuthModule;
use steroids\auth\forms\meta\ChangePasswordChangeFormMeta;
use steroids\auth\UserInterface;
use steroids\core\base\Model;
use steroids\core\validators\PasswordValidator;

class ChangePasswordForm extends ChangePasswordChangeFormMeta
{
    /**
     * @var UserInterface|Model
     */
    public $user;

    public function rules()
    {
        return array_merge(parent::rules(), [
            ['password', PasswordValidator::class],
            ['password', 'compare', 'compareAttribute' => 'passwordAgain', 'when' => fn() => !!$this->passwordAgain],
        ]);
    }

    public function change()
    {
        if ($this->validate()) {
            // Save new password
            $module = AuthModule::getInstance();
            $this->user->setAttribute($module->passwordHashAttribute, \Yii::$app->security->generatePasswordHash($this->password));
            $this->user->saveOrPanic();
        }
    }
}
