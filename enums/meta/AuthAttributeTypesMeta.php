<?php

namespace steroids\auth\enums\meta;

use Yii;
use steroids\core\base\Enum;

abstract class AuthAttributeTypesMeta extends Enum
{
    const EMAIL = 'email';
    const PHONE = 'phone';
    const LOGIN = 'login';

    public static function getLabels()
    {
        return [
            self::EMAIL => Yii::t('app', 'email'),
            self::PHONE => Yii::t('app', 'телефон'),
            self::LOGIN => Yii::t('app', 'логин')
        ];
    }
}
