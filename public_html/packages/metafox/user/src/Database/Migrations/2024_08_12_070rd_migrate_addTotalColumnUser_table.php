<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;
use MetaFox\User\Jobs\MigrateTotalFollowForUserJob;

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
        $this->addColumns();
        $this->updateValues();
    }

    protected function addColumns(): void
    {
        if (Schema::hasColumns('users', ['total_follower', 'total_following'])) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            DbTableHelper::totalColumns($table, ['follower', 'following']);
        });

    }

    protected function updateValues(): void
    {
        MigrateTotalFollowForUserJob::dispatch();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumns('users', ['total_follower', 'total_following'])) {
            Schema::dropColumns('users', ['total_follower', 'total_following']);
        }
    }
};
