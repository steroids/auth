<?php

namespace app\auth\migrations;

use steroids\core\base\Migration;

class M200930083445CreateUserAuthentificatorKeys extends Migration
{
    public function safeUp()
    {
        $this->createTable('user_authenticator_keys', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'secretKey' => $this->string(),
            'authenticatorType' => $this->string(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('user_authenticator_keys');
    }
}
