<?php

namespace steroids\auth\enums\meta;

use Yii;
use steroids\core\base\Enum;

abstract class AuthenticatorEnumMeta extends Enum
{
    const GOOGLE_AUTH = 'google_auth';
    const NOTIFIER_AUTH = 'notifier_auth';

    public static function getLabels()
    {
        return [
            self::GOOGLE_AUTH => Yii::t('app', '2fa аутентификация с использованием google'),
            self::NOTIFIER_AUTH => Yii::t('app', '2fa аутентификация с использованием уведомлений на телефон или почту')
        ];
    }
}
