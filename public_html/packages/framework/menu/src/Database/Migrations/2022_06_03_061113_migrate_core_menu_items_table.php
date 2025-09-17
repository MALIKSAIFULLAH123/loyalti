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
 * @link \MetaFox\Core\Models\MenuItem::$fillable
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('core_menu_items')) {
            Schema::create('core_menu_items', function (Blueprint $table) {
                $table->increments('id');
                DbTableHelper::moduleColumn($table);
                $table->string('menu', 100)->index();
                $table->string('resolution', 8)->default('web'); // web, admin, mobile
                $table->string('parent_name', 200)->nullable();
                $table->string('name', 200);
                $table->string('label', 255)->nullable();
                $table->string('note', 200)->nullable();
                $table->unsignedInteger('ordering')->default(0);
                $table->string('as')->nullable();
                $table->string('testid')->nullable();
                $table->string('value')->nullable();
                $table->string('icon')->nullable();
                $table->string('to')->nullable();
                $table->unsignedTinyInteger('is_active')->default(1);
                $table->mediumText('extra')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->unique(['menu', 'resolution', 'parent_name', 'name'], 'uniq_menu_item');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('core_menu_items');
    }
};
