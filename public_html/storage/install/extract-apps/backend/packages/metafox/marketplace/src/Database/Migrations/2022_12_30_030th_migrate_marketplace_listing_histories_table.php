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
        if (Schema::hasTable('marketplace_listing_histories')) {
            return;
        }

        Schema::create('marketplace_listing_histories', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('listing_id', false, true)
                ->index('history_listing_id');

            DbTableHelper::morphUserColumn($table);

            $table->timestamp('visited_at')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_listing_histories');
    }
};
