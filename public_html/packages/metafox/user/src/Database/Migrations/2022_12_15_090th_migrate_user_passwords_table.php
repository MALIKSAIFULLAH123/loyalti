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
        if (!Schema::hasTable('user_passwords')) {
            Schema::create('user_passwords', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->primary();
                $table->string('password_hash')->nullable();
                $table->string('password_salt')->nullable();
                $table->string('password_method')->nullable();
                $table->mediumText('params')->nullable();
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
        Schema::dropIfExists('user_passwords');
    }
};
