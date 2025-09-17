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
        if (!Schema::hasTable('user_inactive_process')) {
            Schema::create('user_inactive_process', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphColumn($table, 'user');
                $table->integer('round')->default(5);
                $table->integer('status')->default(0);
                $table->integer('total_sent')->default(0);
                $table->integer('total_users')->default(0);

                $table->timestamps();
            });
        }
        if (!Schema::hasTable('user_inactive_process_data')) {
            Schema::create('user_inactive_process_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('process_id');

                DbTableHelper::morphColumn($table, 'user');
                $table->integer('status')->default(0);

                $table->timestamps();
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
        Schema::dropIfExists('user_inactive_process');
    }
};
