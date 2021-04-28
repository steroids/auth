<?php

namespace steroids\auth\enums;

use steroids\auth\enums\meta\AuthAttributeTypeEnumMeta;

class AuthAttributeTypeEnum extends AuthAttributeTypeEnumMeta
{
    public static function getNotifierTypes()
    {
        return [
            self::EMAIL,
            self::PHONE
        ];
    }

    /**
     * @param string $login value of the user's login attributes
     * @return string one of AuthAttributeTypeEnum
     */
    public static function resolveNotifierByLogin(string $login)
    {
        return strpos($login, '@') !== false ? self::EMAIL : self::PHONE;
    }
}
