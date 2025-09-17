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
        if (!Schema::hasTable('user_verify')) {
            return;
        }

        Schema::table('user_verify', function (Blueprint $table) {
            $table->tinyInteger('is_verified')->default(0)->after('email');
            $table->renameColumn('email', 'verifiable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('user_verify')) {
            return;
        }

        Schema::table('user_verify', function (Blueprint $table) {
            $table->renameColumn('verifiable', 'email');
            $table->dropColumn('is_verified');
        });
    }
};
