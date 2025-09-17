<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\User\Models\UserProfile;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasColumn('user_profiles', 'birthday_month')) {
            return;
        }

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->smallInteger('birthday_month')->nullable();
        });

        $this->migrate();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }

    protected function migrate(): void
    {
        match (database_driver()) {
            'mysql' => $this->migrateMysql(),
            default => $this->migratePostgres(),
        };
    }

    private function migrateMysql()
    {
        UserProfile::query()->whereNotNull('birthday')->update([
            'birthday_month' => \Illuminate\Support\Facades\DB::raw("MONTH(birthday)"),
        ]);
    }

    private function migratePostgres()
    {
        UserProfile::query()->whereNotNull('birthday')->update([
            'birthday_month' => \Illuminate\Support\Facades\DB::raw("EXTRACT('MONTH' From birthday)"),
        ]);
    }
};
