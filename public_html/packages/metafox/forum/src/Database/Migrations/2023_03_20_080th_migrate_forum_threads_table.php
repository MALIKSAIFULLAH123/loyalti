<?php

use MetaFox\Forum\Jobs\MigrateStatistic;
use MetaFox\Forum\Jobs\MigratePostId;
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
        if (!Schema::hasTable('forum_threads')) {
            return;
        }

        Schema::table('forum_threads', function (Blueprint $table) {
            $table->bigInteger('first_post_id', false, true)
                ->default(0)
                ->index();
            $table->bigInteger('last_post_id', false, true)
                ->default(0)
                ->index();
        });

        MigrateStatistic::dispatch();

        MigratePostId::dispatch();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('forum_threads')) {
            return;
        }

        Schema::table('forum_threads', function (Blueprint $table) {
            $table->dropColumn(['first_post_id', 'last_post_id']);
        });
    }
};
