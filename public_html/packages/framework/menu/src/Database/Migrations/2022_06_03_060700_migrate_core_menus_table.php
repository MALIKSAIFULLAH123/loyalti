<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * stub: /packages/database/migration.stub.
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \MetaFox\Core\Models\Menu::$fillable
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('core_menus')) {
            Schema::create('core_menus', function (Blueprint $table) {
                $table->increments('id');
                DbTableHelper::moduleColumn($table);
                $table->string('name', 200)->index();
                $table->string('resource_name', 100)->nullable();
                $table->string('resolution', 8)->default('web'); // web, admin, mobile
                $table->unsignedTinyInteger('is_active')->default(1);
                $table->mediumText('extra')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['name', 'resolution'], 'menu_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_menus');
    }
};
