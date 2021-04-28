<?php

namespace steroids\auth\validators;

use yii\validators\RegularExpressionValidator;

class LoginValidator extends RegularExpressionValidator
{
    public $pattern = '/^[a-z0-9-_.]+$/i';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->message === null) {
            $this->message = \Yii::t('steroids', '{attribute} может содержать только латинские буквы, цифры и знаки ".-_".');
        }

        parent::init();
    }
}