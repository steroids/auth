<?php

namespace steroids\auth\migrations;

use steroids\core\base\Migration;

class M200903021656AuthConfirmAddUid extends Migration
{
    public function safeUp()
    {
        $this->addColumn('auth_confirms', 'uid', $this->string(36));
    }

    public function safeDown()
    {
        $this->dropColumn('auth_confirms', 'uid');
    }
}
