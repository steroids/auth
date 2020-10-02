<?php

namespace steroids\auth\forms;

use Yii;
use steroids\auth\AuthModule;
use steroids\auth\forms\meta\Validate2FaCodeMeta;

class Validate2FaCode extends Validate2FaCodeMeta
{
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                ['validate', function($attribute) {
                    $validate = AuthModule::getInstance()->authenticate2FA(
                        Yii::$app->user,
                        Yii::$app->request->post('login'),
                        $this->code
                    );
                    if (!$validate) {
                        $this->addError($attribute, \Yii::t('steroids', 'Валидация не пройдена'));
                    }
                }],
            ]
        );
    }
}
