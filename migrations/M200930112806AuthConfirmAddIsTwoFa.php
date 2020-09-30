<?php

namespace app\auth\migrations;

use steroids\core\base\Migration;

class M200930112806AuthConfirmAddIsTwoFa extends Migration
{
    public function safeUp()
    {
        $this->addColumn('auth_confirms', 'isTwoFa', $this->boolean()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('auth_confirms', 'isTwoFa');
    }
}
