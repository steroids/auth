<?php

namespace steroids\auth\migrations;

use steroids\core\base\Migration;

class M200930112806CreateUniqueIndexInAuthSocials extends Migration
{
    public function safeUp()
    {
        if ($this->db->getTableSchema('auth_socials')) {
            $this->alterColumn('auth_socials', 'externalId', $this->string()->notNull());
            $this->alterColumn('auth_socials', 'socialName', $this->string()->notNull());
            $this->createIndex('auth_socials_socialName_externalId', 'auth_socials', ['socialName', 'externalId'], true);
        }
    }

    public function safeDown()
    {
        if ($this->db->getTableSchema('auth_socials')) {
            $this->alterColumn('auth_socials', 'externalId', $this->string());
            $this->alterColumn('auth_socials', 'socialName', $this->string());
            $this->dropIndex('auth_socials_socialName_externalId', 'auth_socials');
        }
    }
}
