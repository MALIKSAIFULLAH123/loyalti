<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Marketplace\Support\Facade\Listing;

/*
 * @ignore
 * @codeCoverageIgnore
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DbTableHelper::createTagDataTable('marketplace_listing_tag_data');

        if (!Schema::hasTable('marketplace_listings')) {
            Schema::create('marketplace_listings', function (Blueprint $table) {
                $table->id();
                $table->string('title', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
                DbTableHelper::privacyColumn($table);
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                DbTableHelper::featuredColumn($table);
                DbTableHelper::sponsorColumn($table);
                DbTableHelper::tagsColumns($table);
                DbTableHelper::totalColumns($table, ['comment', 'reply', 'like', 'share', 'view', 'attachment']);
                $table->boolean('allow_payment')
                    ->default(false);
                $table->boolean('allow_point_payment')
                    ->default(false);
                $table->boolean('auto_sold')
                    ->default(false);
                $table->boolean('is_sold')
                    ->default(false);
                $table->boolean('is_notified')
                    ->default(false);
                $table->text('price');
                $table->string('short_description', MetaFoxConstant::DEFAULT_MAX_SHORT_DESCRIPTION_LENGTH)
                    ->nullable();
                DbTableHelper::imageColumns($table);
                DbTableHelper::locationColumn($table);
                $table->string('country_iso', 2)
                    ->nullable();
                DbTableHelper::approvedColumn($table);
                $table->timestamp('deleted_at')
                    ->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('marketplace_listing_images')) {
            Schema::create('marketplace_listing_images', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('listing_id');
                DbTableHelper::imageColumns($table);
                $table->unsignedInteger('ordering')->default(1);
            });
        }

        if (!Schema::hasTable('marketplace_invites')) {
            Schema::create('marketplace_invites', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('listing_id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table, true);
                $table->string('method_type', 15)
                    ->nullable();
                $table->string('method_value', 255)
                    ->nullable();
                $table->timestamp('visited_at')
                    ->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('marketplace_invoices')) {
            Schema::create('marketplace_invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('listing_id');
                DbTableHelper::morphUserColumn($table);
                $table->decimal('price', 14, 2)
                    ->default(0.0);
                $table->char('currency_id', 3);
                $table->integer('payment_gateway', false, true)
                    ->default(0);
                $table->string('status', 15)
                    ->index('marketplace_invoice_status');
                $table->timestamp('paid_at')
                    ->nullable();
                $table->timestamps();
            });
        }

        DbTableHelper::categoryTable('marketplace_categories', true);

        DbTableHelper::textTable('marketplace_listing_text');

        DbTableHelper::categoryDataTable('marketplace_category_data');

        DbTableHelper::streamTables('marketplace_listing');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marketplace_listings');
        Schema::dropIfExists('marketplace_invoices');
        Schema::dropIfExists('marketplace_listing_images');
        Schema::dropIfExists('marketplace_invites');
        Schema::dropIfExists('marketplace_categories');
        Schema::dropIfExists('marketplace_category_data');
        Schema::dropIfExists('marketplace_listing_text');
        Schema::dropIfExists('marketplace_listing_tag_data');
        DbTableHelper::dropStreamTables('marketplace_listing');
    }
};
