<?php

namespace steroids\auth\migrations;

use steroids\core\base\Migration;

class M201217033701AuthConfirmDropIs2Fa extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('auth_confirms', 'is2Fa');
    }

    public function safeDown()
    {
        $this->addColumn('auth_confirms', 'is2Fa', $this->boolean()->notNull()->defaultValue(false));
    }
}
