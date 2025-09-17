<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

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
        if (Schema::hasColumn('announcements', 'user_group')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropColumn('user_group');
            });
        }

        if (!Schema::hasTable('announcement_views')) {
            Schema::create('announcement_views', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('announcement_id');
                DbTableHelper::morphUserColumn($table);
                $table->timestamps();

                $table->unique(['user_id', 'announcement_id']);
            });
        }

        if (!Schema::hasTable('announcement_role_data')) {
            Schema::create('announcement_role_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('announcement_id');
                $table->unsignedBigInteger('role_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasColumn('announcements', 'user_group')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->string('user_group', 255)->nullable();
            });
        }

        Schema::dropIfExists('announcement_views');
    }
};
