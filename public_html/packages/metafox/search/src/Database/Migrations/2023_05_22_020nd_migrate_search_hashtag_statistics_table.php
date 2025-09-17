<?php

use MetaFox\Search\Jobs\MigrateHashtagStatisticJob;
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
        if (Schema::hasTable('search_hashtag_statistics')) {
            return;
        }

        Schema::create('search_hashtag_statistics', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('tag_id', false, true)
                ->unique();

            $table->bigInteger('total_item', false, true)
                ->default(0);
        });

        MigrateHashtagStatisticJob::dispatchSync();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('search_hashtag_statistics');
    }
};
