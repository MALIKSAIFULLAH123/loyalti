<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('notification_modules')) {
            Schema::create('notification_modules', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('module_id', 500)->index();
                $table->string('title')->nullable();
                $table->unsignedInteger('is_active')->default(1);
                $table->string('channel')->default('database');
                $table->unsignedInteger('ordering')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notification_type_channels')) {
            Schema::create('notification_type_channels', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('type_id', 500)->index();
                $table->unsignedInteger('is_active')->default(1);
                $table->string('channel')->default('database');
                $table->unsignedInteger('ordering')->default(1);
                $table->timestamps();

                $table->unique(['type_id', 'channel']);
            });
        }

        if (!Schema::hasTable('notification_module_settings')) {
            Schema::create('notification_module_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                $table->string('module_id', 500)->nullable()->index();
                $table->mediumText('user_value')->nullable(true);
            });
        }
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_modules');
        Schema::dropIfExists('notification_module_settings');
        Schema::dropIfExists('notification_type_channels');
    }
};
