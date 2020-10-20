<?php

namespace steroids\auth\migrations;

use steroids\core\base\Migration;

class M200930082836Auth2FaValidation extends Migration
{
    public function safeUp()
    {
        $this->createTable('auth_2fa_validation', [
            'id' => $this->primaryKey(),
            'createTime' => $this->dateTime(),
            'authenticatorType' => $this->string(),
            'userId' => $this->integer(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('auth_2fa_validation');
    }
}
