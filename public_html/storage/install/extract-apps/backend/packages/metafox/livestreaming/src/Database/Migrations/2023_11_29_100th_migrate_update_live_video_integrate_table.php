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
        if (!Schema::hasTable('livestreaming_live_videos') || Schema::hasColumn('livestreaming_live_videos', 'to_story')) {
            return;
        }
        Schema::table('livestreaming_live_videos', function (Blueprint $table) {
            $table->unsignedTinyInteger('to_story')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('livestreaming_live_videos') || !Schema::hasColumn('livestreaming_live_videos', 'to_story')) {
            return;
        }
        Schema::table('livestreaming_live_videos', function (Blueprint $table) {
            $table->dropColumn('to_story');
        });
    }
};
