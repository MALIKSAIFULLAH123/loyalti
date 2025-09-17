<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Notification\Models\TypeChannel;

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
        if (!Schema::hasColumn('notification_type_channels', 'type_id')) {
            return;
        }

        $dbDriver = config('database.default');
        match ($dbDriver) {
            'mysql' => $this->mySqlUp(),
            'pgsql' => $this->postgreSqlUp(),
        };
    }

    protected function mySqlUp(): void
    {
        Schema::table('notification_type_channels', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->change();
        });
    }

    protected function postgreSqlUp(): void
    {
        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table  = $prefix ? $prefix . 'notification_type_channels' : 'notification_type_channels';

        $sql = 'ALTER TABLE %s ALTER COLUMN type_id TYPE BIGINT USING type_id::BIGINT';

        \Illuminate\Support\Facades\DB::statement(sprintf($sql, $table));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
