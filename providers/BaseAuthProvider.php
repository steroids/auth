<?php

namespace steroids\auth\providers;

use steroids\auth\AuthProfile;
use yii\base\Component;

abstract class BaseAuthProvider extends Component
{
    public $name;

    /**
     * @param array $params
     * @return AuthProfile
     */
    public abstract function auth(array $params);

    /**
     * @return array|null
     */
    public function getClientConfig()
    {
        return null;
    }

}
