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
        $this->createIapProduct();
        $this->createIapOrder();
    }

    public function createIapProduct(): void
    {
        if (Schema::hasTable('iap_products')) {
            return;
        }

        Schema::create('iap_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255);
            $table->text('price');
            DbTableHelper::morphItemColumn($table, true);
            $table->string('ios_product_id', 255)->nullable();
            $table->string('android_product_id', 255)->nullable();
            $table->unsignedTinyInteger('is_recurring')->default(0);
            $table->unique(['item_id', 'item_type'], 'unique_item');
            $table->timestamps();
        });
    }

    public function createIapOrder(): void
    {
        if (Schema::hasTable('iap_orders')) {
            return;
        }

        Schema::create('iap_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            DbTableHelper::morphUserColumn($table);
            $table->string('platform', 50);
            $table->string('product_id', 255)->nullable();
            $table->unsignedInteger('payment_order_id');
            $table->string('purchase_token', 255)->nullable();
            $table->string('original_transaction_id', 255)->nullable();
            $table->string('transaction_id', 255)->nullable();
            $table->unsignedTinyInteger('is_recurring')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('iap_products');
        Schema::dropIfExists('iap_orders');
    }
};
