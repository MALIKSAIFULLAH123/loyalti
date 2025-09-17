<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFox\User\Models\User;

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
        if (!Schema::hasTable('users') || !Schema::hasTable('user_profiles')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 64)->nullable()->after('email_verified_at');
            }

            if (!Schema::hasColumn('users', 'phone_number_verified_at')) {
                $table->timestamp('phone_number_verified_at')->nullable()->after('phone_number');
            }

            if (!Schema::hasColumn('users', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('phone_number_verified_at');
            }
        });

        $this->migrate();

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('user_profiles')) {
            return;
        }

        Schema::table('user_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('user_profiles', 'phone_number')) {
                $table->string('phone_number')->nullable();
            }
        });

        $this->rollback();

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_number');
            $table->dropColumn('phone_number_verified_at');
            $table->dropColumn('verified_at');
        });
    }

    private function migrate()
    {
        User::withoutEvents(function () {
            User::query()->update([
                'verified_at' => DB::raw('email_verified_at'),
            ]);
        });

        return match (database_driver()) {
            'mysql' => $this->migrateMysql(),
            default => $this->migratePostgres(),
        };
    }

    private function rollback()
    {
        return match (database_driver()) {
            'mysql' => $this->rollbackMysql(),
            default => $this->rollbackPostgres(),
        };
    }

    private function migrateMysql()
    {
        return DB::statement('UPDATE users JOIN user_profiles ON users.id = user_profiles.id SET users.phone_number = user_profiles.phone_number');
    }

    private function migratePostgres()
    {
        return DB::statement('UPDATE users SET phone_number = user_profiles.phone_number FROM user_profiles WHERE users.id = user_profiles.id');
    }

    private function rollbackMysql()
    {
        return DB::statement('UPDATE user_profiles JOIN users ON users.id = user_profiles.id SET user_profiles.phone_number = users.phone_number');
    }

    private function rollbackPostgres()
    {
        return DB::statement('UPDATE user_profiles SET phone_number = users.phone_number FROM users WHERE users.id = user_profiles.id');
    }
};
