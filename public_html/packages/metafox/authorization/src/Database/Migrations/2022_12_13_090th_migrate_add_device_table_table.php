<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        Schema::dropIfExists('firebase_devices');

        if (Schema::hasTable('core_user_devices')) {
            return;
        }

        Schema::create('core_user_devices', function (Blueprint $table) {
            $table->bigIncrements('id');
            DbTableHelper::morphUserColumn($table);
            $table->string('device_token');
            $table->string('device_id')->default('');
            $table->string('device_uid')->nullable();
            $table->string('token_source')->default('');
            $table->string('platform', 128)->default('ios');
            $table->mediumInteger('platform_version')->nullable();
            DbTableHelper::activeColumn($table);
            $table->mediumText('extra')->nullable();
            $table->timestamps();

            $table->index(['device_token', 'device_id'], 'core_token_device_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('core_user_devices');
    }
};
