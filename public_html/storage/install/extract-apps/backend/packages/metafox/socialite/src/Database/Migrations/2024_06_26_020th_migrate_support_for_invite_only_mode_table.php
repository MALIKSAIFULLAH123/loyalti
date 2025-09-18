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
        if (!Schema::hasTable('social_accounts')) {
            return;
        }

        Schema::table('social_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('social_accounts', 'extra')) {
                $table->mediumText('extra')->nullable();
            }

            if (!Schema::hasColumn('social_accounts', 'hash')) {
                $table->string('hash')->unique()->nullable();
            }

            if (!Schema::hasColumn('social_accounts', 'hash_expired_at')) {
                $table->timestamp('hash_expired_at')->nullable();
            }

            if (Schema::hasColumn('social_accounts', 'user_id')) {
                $table->string('user_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumns('social_accounts', ['extra', 'hash', 'user_id', 'hash_expired_at'])) {
            Schema::table('social_accounts', function (Blueprint $table) {
                $table->dropColumn(['extra', 'hash', 'hash_expired_at']);
                $table->string('user_id')->nullable(false)->change();
            });
        }
    }
};
