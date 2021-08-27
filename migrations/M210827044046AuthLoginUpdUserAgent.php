<?php

namespace steroids\auth\migrations;

use steroids\core\base\Migration;

class M210827044046AuthLoginUpdUserAgent extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('auth_logins', 'userAgent', $this->string(2000));
    }

    public function safeDown()
    {
        $this->alterColumn('auth_logins', 'userAgent', $this->string());
    }
}
