<?php

namespace steroids\auth\migrations;

use steroids\core\base\Migration;

class M200930083445CreateUserAuthentificatorKeys extends Migration
{
    public function safeUp()
    {
        $this->createTable('auth_authenticator_keys', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'secretKey' => $this->string(),
            'authenticatorType' => $this->string(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('auth_authenticator_keys');
    }
}
