<?php

namespace steroids\auth\migrations;

use steroids\core\base\Migration;

class M000000000001Auth extends Migration
{
    public $usersTable = 'users';

    public function safeUp()
    {
        $this->upLogins();
        $this->upConfirms();
        $this->upSocials();
    }

    public function safeDown()
    {
        $this->downLogins();
        $this->downConfirms();
        $this->downSocials();
    }

    protected function upLogins()
    {
        if (!$this->db->getTableSchema('auth_logins')) {
            $this->createTable('auth_logins', [
                'id' => $this->primaryKey(),
                'userId' => $this->integer(),
                'authId' => $this->integer(),
                'accessToken' => $this->string(64),
                'wsToken' => $this->string(16),
                'ipAddress' => $this->string(64),
                'location' => $this->string(),
                'userAgent' => $this->string(),
                'createTime' => $this->dateTime(),
                'expireTime' => $this->dateTime(),
            ]);
        }
    }

    protected function upConfirms()
    {
        if (!$this->db->getTableSchema('auth_confirms')) {
            $this->createTable('auth_confirms', [
                'id' => $this->primaryKey(),
                'userId' => $this->integer(),
                'value' => $this->string(),
                'type' => $this->string(10),
                'code' => $this->string(32),
                'isConfirmed' => $this->boolean()->notNull()->defaultValue(false),
                'createTime' => $this->dateTime(),
                'updateTime' => $this->dateTime(),
                'expireTime' => $this->dateTime(),
            ]);
        }
    }

    protected function upSocials()
    {
        if (!$this->db->getTableSchema('auth_socials')) {
            $this->createTable('auth_socials', [
                'id' => $this->primaryKey(),
                'uid' => $this->string(36),
                'userId' => $this->integer(),
                'externalId' => $this->string(),
                'socialName' => $this->string(),
                'profileJson' => $this->text(),
                'createTime' => $this->dateTime(),
                'updateTime' => $this->dateTime(),
            ]);
        }
    }

    protected function downLogins()
    {
        $this->dropTable('auth_logins');
    }

    protected function downConfirms()
    {
        $this->dropTable('auth_confirms');
    }

    protected function downSocials()
    {
        $this->dropTable('auth_socials');
    }
}