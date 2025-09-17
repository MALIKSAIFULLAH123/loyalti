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
        if (!Schema::hasColumn('user_custom_options', 'label')) {
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
        Schema::table('user_custom_options', function (Blueprint $table) {
            $table->string('label', 150)->change();
        });
    }

    protected function postgreSqlUp(): void
    {
        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table  = $prefix ? $prefix . 'user_custom_options' : 'user_custom_options';

        $sql = 'ALTER TABLE %s ALTER COLUMN label TYPE VARCHAR (150)';

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
