<?php

namespace MetaFox\ActivityPoint\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigratePaymentGatewayId
{
    public function handle(): void
    {
        if (!Schema::hasTable('apt_package_purchases')) {
            return;
        }

        try {
            match (database_driver()) {
                'pgsql' => $this->migratePostgres(),
                default => $this->migrateMysql(),
            };
        } catch (\Exception $e) {
            Log::channel('dev')->info($e->getMessage());
        }
    }

    private function migrateMysql(): void
    {
        DB::statement("
            UPDATE payment_orders
            JOIN apt_package_purchases ON payment_orders.item_id = apt_package_purchases.id
            SET payment_orders.gateway_id = apt_package_purchases.gateway_id
            WHERE
              payment_orders.item_type = 'activitypoint_package_purchase'
              AND payment_orders.gateway_id = 0
              AND payment_orders.status <> 'init'
        ");

        DB::statement("
            UPDATE payment_transactions
            JOIN payment_orders ON payment_transactions.order_id = payment_orders.id
            SET payment_transactions.gateway_id = payment_orders.gateway_id
            WHERE
              payment_orders.item_type = 'activitypoint_package_purchase'
              AND payment_transactions.gateway_id = 0
        ");
    }

    private function migratePostgres(): void
    {
        DB::statement("
            UPDATE
              payment_orders
            SET
              gateway_id = apt_package_purchases.gateway_id
            FROM
              apt_package_purchases
            WHERE
              payment_orders.item_type = 'activitypoint_package_purchase'
              AND payment_orders.gateway_id = 0
              AND payment_orders.status <> 'init'
              AND payment_orders.item_id = apt_package_purchases.id
        ");

        DB::statement("
            UPDATE
              payment_transactions
            SET
              gateway_id = payment_orders.gateway_id
            FROM
              payment_orders
            WHERE
              payment_orders.item_type = 'activitypoint_package_purchase'
              AND payment_transactions.gateway_id = 0
              AND payment_transactions.order_id = payment_orders.id
        ");
    }
}
