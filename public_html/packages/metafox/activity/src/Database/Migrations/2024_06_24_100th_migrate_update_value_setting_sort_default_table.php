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
        $this->addNewColumn();
        $this->migrateUp();
    }

    protected function addNewColumn(): void
    {
        if (Schema::hasColumn('activity_feeds', 'latest_activity_at')) {
            return;
        }

        Schema::table('activity_feeds', function (Blueprint $table) {
            $table->dateTime('latest_activity_at')->nullable()->after('updated_at');
        });
    }

    protected function migrateUp(): void
    {
        if (!Schema::hasColumn('activity_feeds', 'latest_activity_at')) {
            return;
        }

        DB::statement('UPDATE activity_feeds SET latest_activity_at = updated_at where latest_activity_at is null');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn('activity_feeds', 'latest_activity_at')) {
            Schema::dropColumns('activity_feeds', 'latest_activity_at');
        }
    }
};
