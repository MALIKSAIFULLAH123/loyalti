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
        if (Schema::hasTable('video_verify_processes')) {
            return;
        }

        Schema::create('video_verify_processes', function (Blueprint $table) {
            $table->bigIncrements('id');

            DbTableHelper::morphColumn($table, 'user');
            $table->string('status')->default('pending');
            $table->integer('total_verified')->default(0);
            $table->unsignedInteger('last_id')->default(0);
            $table->integer('total_videos')->default(0);
            $table->json('extra')->nullable();

            $table->timestamps();
        });

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('video_verify_processes');
    }
};
