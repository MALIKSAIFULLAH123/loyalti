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
        if (!Schema::hasColumn('group_answers', 'request_id')) {
            Schema::table('group_answers', function (Blueprint $table) {
                $table->unsignedInteger('request_id')->index()->nullable();
            });
        }
        if (Schema::hasColumn('group_answers', 'value')) {
            Schema::table('group_answers', function (Blueprint $table) {
                $table->mediumText('value')->nullable()->change();
            });
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
        if (Schema::hasColumn('group_answers', 'request_id')) {
            Schema::table('group_answers', function (Blueprint $table) {
                $table->dropColumn(['request_id']);
            });
        }
    }
};
