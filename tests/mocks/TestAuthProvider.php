<?php

namespace steroids\auth\tests\mocks;

use steroids\auth\AuthProfile;
use steroids\auth\providers\BaseAuthProvider;

class TestAuthProvider extends BaseAuthProvider
{
    protected static $params;

    /**
     * @inheritDoc
     */
    public function auth(array $params)
    {
        if (!static::$params) {
            static::$params = [
                'id' => 'test-exte' . time() . 'rnal-id',
                'email' => 'test' . time() . '@email.com',
                'name' => 'test' . time() . 'Username',
                'avatarUrl' => 'picture-url.com',
            ];
        }
        return new AuthProfile(static::$params);
    }
}
