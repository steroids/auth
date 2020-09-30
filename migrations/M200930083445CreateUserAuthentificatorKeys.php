<?php

namespace app\auth\migrations;

use steroids\core\base\Migration;

class M200930083445CreateUserAuthentificatorKeys extends Migration
{
    public function safeUp()
    {
        $this->createTable('user_authentificator_keys', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'secretKey' => $this->string(),
            'authentificatorType' => $this->string(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('user_authentificator_keys');
    }
}
