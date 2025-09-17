<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        if (!Schema::hasTable('users')) {
            return;
        }

        if (!Schema::hasColumn('users', 'search_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('search_name')->nullable()->index();
            });
        }

        if (!Schema::hasColumn('users', 'search_name')) {
            return;
        }

        DB::statement("
                UPDATE users 
                SET search_name = COALESCE(NULLIF(full_name, ''), user_name)
            ");

        Schema::table('users', function (Blueprint $table) {
            $table->string('search_name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['search_name']);
            $table->dropColumn('search_name');
        });
    }
};
