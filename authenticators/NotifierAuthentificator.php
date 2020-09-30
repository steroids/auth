<?php


namespace steroids\auth\models;


class NotifierAuthentificator extends BaseAuthentificator
{
    public function getType()
    {
        return 'NotifierAuth';
    }

    public function sendCode()
    {
        //@todo send sms or email notification
        return '';
    }
}