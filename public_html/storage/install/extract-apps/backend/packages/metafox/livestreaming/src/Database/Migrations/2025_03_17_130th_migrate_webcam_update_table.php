<?php

use MetaFox\Platform\Support\DbTableHelper;
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
        if (!Schema::hasTable('livestreaming_live_videos') || Schema::hasColumn('livestreaming_live_videos', 'webcam_config')) {
            return;
        }

        Schema::table('livestreaming_live_videos', function (Blueprint $table) {
            $table->text('webcam_config')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('livestreaming_live_videos') || !Schema::hasColumn('livestreaming_live_videos', 'webcam_config')) {
            return;
        }

        Schema::dropColumns('livestreaming_live_videos', 'webcam_config');
    }
};
