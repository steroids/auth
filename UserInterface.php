<?php

namespace steroids\auth;

use steroids\core\base\Model;
use yii\web\IdentityInterface;

interface UserInterface extends IdentityInterface
{
    /**
     * @param string $login
     * @param array $attributes
     * @return UserInterface|Model
     */
    public static function findBy(string $login, array $attributes);

    /**
     * @param string $password
     * @return bool
     */
    public function validatePassword($password);

    /**
     * @param string $templateName
     * @param array $params
     * @return void
     */
    public function sendNotify($templateName, $params = []);
}
