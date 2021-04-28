<?php

namespace steroids\auth\migrations;

use yii\db\Migration;

class M201221074023AddPrevId extends Migration
{
    public function safeUp()
    {
        $this->addColumn('auth_confirms', 'prevId', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn('auth_confirms', 'prevId');
    }
}
