<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
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
        if (!Schema::hasTable('payment_orders')) {
            return;
        }

        if (!Schema::hasTable('payment_transactions')) {
            return;
        }

        if (!Schema::hasTable('payment_gateway')) {
            return;
        }

        try {
            app('events')->dispatch('payment.migrate_payment_gateway_id');
        } catch (\Exception $e) {
            Log::channel('dev')->info($e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
