<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models\
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if ('pgsql' != DB::getDriverName()) {
            return;
        }
        if (!Schema::hasTable('like_reactions')) {
            return;
        }

        if (!DB::table('like_reactions')->exists()) {
            return;
        }

        $nextval = (int) (DB::table('like_reactions')->max('id'));

        if (!$nextval) {
            return;
        }

        DB::statement("SELECT setval('like_reactions_id_seq', $nextval)");
        // to do here
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
