<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models\
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('bundle_tasks')) {
            return;
        }

        Schema::create('bundle_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->nullable(true);
            $table->string('reason');
            $table->mediumText('data');
            $table->mediumText('bundle_url')->nullable(true);
            $table->mediumText('log_url')->nullable();
            $table->mediumText('result')->nullable(true);
            $table->mediumText('attachments')->nullable(true);
            $table->string('bundle_status', 64)
                ->default('initial');
            $table->string('bundle_disk')->nullable(true);
            $table->string('bundle_path')->nullable(true);
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
        Schema::dropIfExists('bundle_tasks');
    }
};
