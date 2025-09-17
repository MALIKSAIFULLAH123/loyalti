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
        if (Schema::hasTable('activity_schedules')) {
            return;
        }
        Schema::create('activity_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            DbTableHelper::setupResourceColumns($table, true, true, false, false);
            $table->string('post_type')->nullable();
            $table->text('data');
            DbTableHelper::feedContentColumn($table);
            $table->timestamp('schedule_time');
            $table->unsignedInteger('is_temp')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_schedules');
    }
};
