<?php


namespace steroids\auth\validators;


use steroids\auth\AuthModule;
use yii\validators\Validator;

class ReCaptchaValidator extends Validator
{

    public AuthModule $module;
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->module = AuthModule::getInstance();

        if ($this->message === null) {
            $this->message = \Yii::t('steroids', 'Каптча не пройдена');
        }
    }

    public function validateAttribute($model, $attribute)
    {
        if($this->module->isCaptchaEnable && !(new $this->module->captcha['class'])->validate($model->$attribute)){
            $this->addError($model, $attribute, $this->message);
        }
    }
}