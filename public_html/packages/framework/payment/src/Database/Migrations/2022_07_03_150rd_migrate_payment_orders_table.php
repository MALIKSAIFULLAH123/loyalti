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
        if (Schema::hasTable('payment_orders')) {
            return;
        }

        Schema::create('payment_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('gateway_id')->index()->default(0);
            DbTableHelper::morphUserColumn($table);
            DbTableHelper::morphItemColumn($table);

            $table->string('title');
            DbTableHelper::pricingColumns($table, 'total');
            $table->enum('payment_type', ['onetime', 'recurring'])
                ->default('onetime')
                ->index();
            $table->enum('status', ['init', 'pending_approval', 'pending_payment', 'completed', 'cancelled', 'failed'])
                ->default('init')
                ->index();
            $table->enum('recurring_status', ['unset', 'pending', 'active', 'failed', 'ended', 'cancelled'])
                ->default('unset')
                ->index();
            $table->string('gateway_subscription_id')->nullable();
            $table->string('gateway_order_id')->nullable();

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
        Schema::dropIfExists('payment_orders');
    }
};
