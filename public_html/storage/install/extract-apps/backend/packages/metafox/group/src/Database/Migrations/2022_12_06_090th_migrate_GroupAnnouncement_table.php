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
        if (!Schema::hasTable('group_announcements')) {
            Schema::create('group_announcements', function (Blueprint $table) {
                $table->integerIncrements('id');
                $table->unsignedInteger('group_id')->index();
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphItemColumn($table);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('group_announcement_hidden')) {
            Schema::create('group_announcement_hidden', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                $table->unsignedBigInteger('announcement_id');
                $table->unsignedInteger('group_id')->index();
                $table->timestamps();
                $table->unique(['user_id', 'announcement_id']);
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
        Schema::dropIfExists('group_announcements');
    }
};
