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
        if (Schema::hasTable('music_songs')) {
            Schema::table('music_songs', function (Blueprint $table) {
                DbTableHelper::featuredColumn($table);
                DbTableHelper::sponsorColumn($table);
                DbTableHelper::approvedColumn($table);
                $table->unsignedInteger('total_attachment')->default(0);
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
        if (Schema::hasColumns(
            'music_songs',
            ['is_featured', 'is_sponsor', 'is_approved', 'total_attachment', 'featured_at', 'sponsor_in_feed']
        )) {
            Schema::table('music_songs', function (Blueprint $table) {
                $table->dropColumn(
                    ['is_featured', 'is_sponsor', 'is_approved', 'total_attachment', 'featured_at', 'sponsor_in_feed']
                );
            });
        }
    }
};
