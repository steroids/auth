<?php

namespace steroids\auth\tests\mocks;

use steroids\auth\AuthProfile;
use steroids\auth\providers\BaseAuthProvider;

class TestAuthProvider extends BaseAuthProvider
{

    /**
     * @inheritDoc
     */
    public function auth(array $params)
    {
        return new AuthProfile([
            'id' => 'test-external-id',
            'email' => 'test@email.com',
            'name' => 'testUsername',
        ]);
    }
}
