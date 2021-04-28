<?php

namespace steroids\auth\migrations;

use steroids\core\base\Migration;

class M201216035119AuthConfirmAddIsReused extends Migration
{
    public function safeUp()
    {
        $this->addColumn('auth_confirms', 'isReused', $this->boolean()->notNull()->defaultValue(false));
    }

    public function safeDown()
    {
        $this->dropColumn('auth_confirms', 'isReused');
    }
}
