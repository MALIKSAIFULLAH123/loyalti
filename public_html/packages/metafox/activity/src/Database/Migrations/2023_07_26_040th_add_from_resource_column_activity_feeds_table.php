<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Models\Share;
use MetaFox\Core\Models\Link;

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
        if (!Schema::hasTable('activity_feeds')) {
            return;
        }

        Schema::table('activity_feeds', function (Blueprint $table) {
            $table->string('from_resource', 30)->default(Feed::FROM_APP_RESOURCE)->nullable();
        });

        Feed::query()->whereIn('item_type', [Post::ENTITY_TYPE, Share::ENTITY_TYPE, Link::ENTITY_TYPE])
            ->update(['from_resource' => Feed::FROM_FEED_RESOURCE]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('activity_feeds')) {
            return;
        }

        Schema::table('activity_feeds', function (Blueprint $table) {
            $table->dropColumn('from_resource');
        });
    }
};
