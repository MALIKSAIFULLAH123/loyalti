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
        if (!Schema::hasTable('log_messages')) {
            Schema::create('log_messages', function (Blueprint $table) {
                $table->id();
                $table->string('env', 16)->nullable()->default('local');
                $table->string('level_name');
                $table->unsignedSmallInteger('level');
                $table->mediumText('message');
                $table->dateTime('timestamp');
                $table->json('context');
                $table->json('extra');
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
        Schema::dropIfExists('log_messages');
    }
};
