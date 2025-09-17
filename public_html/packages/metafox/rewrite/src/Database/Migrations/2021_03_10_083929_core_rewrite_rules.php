<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * @ignore
 * @codeCoverageIgnore
 */
return new class () extends Migration {
    public function up()
    {
        if (!Schema::hasTable('core_rewrite_rules')) {
            Schema::create('core_rewrite_rules', function (Blueprint $table) {
                $table->increments('id');
                $table->string('from_path');
                $table->string('to_path');
                $table->string('to_mobile_path');
                DbTableHelper::moduleColumn($table);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('core_rewrite_rules');
    }
};
