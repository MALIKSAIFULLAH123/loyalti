<?php

namespace MetaFox\Advertise\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigratePaymentGatewayId
{
    public function handle(): void
    {
        if (!Schema::hasTable('advertise_invoices')) {
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
            JOIN advertise_invoices ON payment_orders.item_id = advertise_invoices.id
            SET payment_orders.gateway_id = advertise_invoices.payment_gateway
            WHERE
              payment_orders.item_type = 'advertise_invoice'
              AND payment_orders.gateway_id = 0
              AND payment_orders.status <> 'init'
        ");

        DB::statement("
            UPDATE payment_transactions
            JOIN payment_orders ON payment_transactions.order_id = payment_orders.id
            SET payment_transactions.gateway_id = payment_orders.gateway_id
            WHERE
              payment_orders.item_type = 'advertise_invoice'
              AND payment_transactions.gateway_id = 0
        ");
    }

    private function migratePostgres(): void
    {
        DB::statement("
            UPDATE
              payment_orders
            SET
              gateway_id = advertise_invoices.payment_gateway
            FROM
              advertise_invoices
            WHERE
              payment_orders.item_type = 'advertise_invoice'
              AND payment_orders.gateway_id = 0
              AND payment_orders.status <> 'init'
              AND payment_orders.item_id = advertise_invoices.id
        ");

        DB::statement("
            UPDATE
              payment_transactions
            SET
              gateway_id = payment_orders.gateway_id
            FROM
              payment_orders
            WHERE
              payment_orders.item_type = 'advertise_invoice'
              AND payment_transactions.gateway_id = 0
              AND payment_transactions.order_id = payment_orders.id
        ");
    }
}
