<?php


namespace steroids\auth\exceptions;


use Yii;
use yii\base\Exception;

class ConfirmCodeAlreadySentException extends Exception
{
    public static function getDefaultMessage()
    {
        return Yii::t('steroids', 'Код уже был отправлен');
    }
}