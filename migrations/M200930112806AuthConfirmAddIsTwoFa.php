<?php

namespace steroids\auth\migrations;

use steroids\core\base\Migration;

class M200930112806AuthConfirmAddIsTwoFa extends Migration
{
    public function safeUp()
    {
        $this->addColumn('auth_confirms', 'is2Fa', $this->boolean()->notNull()->defaultValue(false));
    }

    public function safeDown()
    {
        $this->dropColumn('auth_confirms', 'is2Fa');
    }
}
