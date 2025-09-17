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
 * @SuppressWarnings(PHPMD)
 * @link \MetaFox\Core\Models\Driver
 * @ignore
 * @codeCoverageIgnore
 */
return new class () extends Migration {
    public function up()
    {
        if (!Schema::hasTable('core_drivers')) {
            Schema::create('core_drivers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type', 32);
                $table->string('resolution', 16)->nullable();
                $table->string('name', 128);
                $table->string('version', 16)->default('*');
                $table->unsignedTinyInteger('is_active')->default(1);
                $table->unsignedTinyInteger('is_preload')->default(0);

                $table->string('alias', 64)->nullable();
                $table->string('title')->nullable();
                $table->string('description')->default('');
                $table->string('driver')->nullable();
                $table->string('url')->nullable();
                $table->string('category', 32)->nullable(true);
                DbTableHelper::moduleColumn($table);
                $table->timestamps();
                $table->unique(['type', 'name', 'version', 'resolution']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('core_drivers');
    }
};
