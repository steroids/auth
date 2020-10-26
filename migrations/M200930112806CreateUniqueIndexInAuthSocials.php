<?php

namespace steroids\auth\migrations;

use steroids\core\base\Migration;

class M200930112806CreateUniqueIndexInAuthSocials extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('auth_socials', 'externalId', $this->string()->notNull());
        $this->alterColumn('auth_socials', 'socialName', $this->string()->notNull());
        $this->createIndex('auth_socials_socialName_externalId', 'auth_socials', ['socialName', 'externalId'], true);
    }

    public function safeDown()
    {
        $this->alterColumn('auth_socials', 'externalId', $this->string());
        $this->alterColumn('auth_socials', 'socialName', $this->string());
        $this->dropIndex('auth_socials_socialName_externalId', 'auth_socials');
    }
}
