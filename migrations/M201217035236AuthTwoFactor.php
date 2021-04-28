<?php

namespace steroids\auth\migrations;

use steroids\core\base\Migration;

class M201217035236AuthTwoFactor extends Migration
{
    public function safeUp()
    {
        $this->dropTable('auth_2fa_validation');
        $this->dropTable('auth_authenticator_keys');
        $this->createTable('auth_two_factor', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'providerName' => $this->string(),
            'providerSecret' => $this->text(),
            'isConfirmed' => $this->boolean()->notNull()->defaultValue(false),
            'createTime' => $this->dateTime(),
            'expireTime' => $this->dateTime(),
        ]);
    }

    public function safeDown()
    {
        $this->createTable('auth_2fa_validation', [
            'id' => $this->primaryKey(),
            'createTime' => $this->dateTime(),
            'authenticatorType' => $this->string(),
            'userId' => $this->integer(),
        ]);
        $this->createTable('auth_authenticator_keys', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'secretKey' => $this->string(),
            'authenticatorType' => $this->string(),
        ]);
        $this->dropTable('auth_two_factor');
    }
}
