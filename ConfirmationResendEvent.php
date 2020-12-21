<?php

namespace steroids\auth;

use steroids\auth\models\AuthConfirm;
use yii\base\Event;

class ConfirmationResendEvent extends Event
{
    /**
     * @var AuthConfirm confirmation that was resend
     */
    public AuthConfirm $prevConfirm;

    /**
     * @var AuthConfirm new confirmation
     */
    public AuthConfirm $newConfirm;
}