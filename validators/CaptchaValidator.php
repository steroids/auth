<?php

namespace steroids\auth\validators;

use steroids\auth\AuthModule;
use yii\validators\Validator;

class CaptchaValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = \Yii::t('steroids', 'Проверка на робота не пройдена');
        }
    }

    public function validateAttribute($model, $attribute)
    {
        $module = AuthModule::getInstance();
        if ($module->captcha && !$module->captcha->validate($model->$attribute)) {
            $this->addError($model, $attribute, $this->message);
        }
    }
}