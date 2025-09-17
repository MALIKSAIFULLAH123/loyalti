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
        if (Schema::hasTable('comments') && !Schema::hasColumn('comments', 'is_edited')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->unsignedTinyInteger('is_edited')->default(0);
            });

            if (DB::getDriverName() == 'pgsql') {
                DB::statement('update comments
                set is_edited=1
                from comment_histories
                where comment_histories.comment_id=comments.id');
            } else {
                DB::statement('update comments
                inner join comment_histories on comment_histories.comment_id=comments.id
                set is_edited=1');
            }
        }

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('comments') && Schema::hasColumn('comments', 'is_edited')) {
            Schema::dropColumns('comments', 'is_edited');
        }
    }
};
