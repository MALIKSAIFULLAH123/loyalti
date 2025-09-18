<?php

use MetaFox\Marketplace\Jobs\MigratePriceJob;
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
        if (Schema::hasTable('marketplace_listing_prices')) {
            return;
        }

        Schema::create('marketplace_listing_prices', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('listing_id', false, true)
                ->index('listing_id');

            $table->char('currency_id', 3)
                ->index('currency_id');

            $table->decimal('price', 14, 2, true)
                ->default(0);

            $table->unique(['listing_id', 'currency_id'], 'listing_price');
        });

        MigratePriceJob::dispatch();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_listing_prices');
    }
};
