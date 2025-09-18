<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Marketplace\Support\Facade\Listing as Facade;

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
        if (Schema::hasTable('marketplace_invoice_transactions')) {
            return;
        }

        Schema::create('marketplace_invoice_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_id', false, true);
            $table->string('status', 15)
                ->index('marketplace_transaction_status');
            $table->decimal('price', 14, 2, true);
            $table->char('currency_id', 3);
            $table->text('transaction_id')
                ->nullable();
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
        Schema::dropIfExists('marketplace_invoice_transactions');
    }
};
