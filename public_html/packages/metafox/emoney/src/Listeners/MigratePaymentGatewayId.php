<?php

namespace MetaFox\EMoney\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\Payment\Models\Order;

class MigratePaymentGatewayId
{
    public function handle(): void
    {
        if (!Schema::hasTable('emoney_withdraw_requests')) {
            return;
        }

        try {
            Order::query()
                ->where('item_type', WithdrawRequest::ENTITY_TYPE)
                ->where('gateway_id', 0)
                ->where('payment_orders.status', '<>', 'init')
                ->join('emoney_withdraw_requests', 'emoney_withdraw_requests.id', '=', 'payment_orders.item_id')
                ->join('payment_gateway', 'payment_gateway.service', '=', 'emoney_withdraw_requests.withdraw_service')
                ->select(['payment_orders.*', 'payment_gateway.id AS payment_gateway_id'])
                ->get()
                ->each(function ($order) {
                    $paymentGatewayId = $order->payment_gateway_id ?? null;

                    if ($paymentGatewayId) {
                        $order->update(['gateway_id' => $paymentGatewayId]);
                    }
                });

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
            UPDATE payment_transactions
            JOIN payment_orders ON payment_transactions.order_id = payment_orders.id
            SET payment_transactions.gateway_id = payment_orders.gateway_id
            WHERE
              payment_orders.item_type = 'ewallet_withdraw_request'
              AND payment_transactions.gateway_id = 0
        ");
    }

    private function migratePostgres(): void
    {
        DB::statement("
            UPDATE
              payment_transactions
            SET
              gateway_id = payment_orders.gateway_id
            FROM
              payment_orders
            WHERE
              payment_orders.item_type = 'ewallet_withdraw_request'
              AND payment_transactions.gateway_id = 0
              AND payment_transactions.order_id = payment_orders.id
        ");
    }
}
