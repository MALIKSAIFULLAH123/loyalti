<?php

namespace MetaFox\EMoney\Listeners;

use Illuminate\Support\Facades\Log;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Repositories\TransactionRepositoryInterface;
use MetaFox\EMoney\Repositories\WithdrawRequestRepositoryInterface;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;

class PaymentSuccessListener
{
    public function __construct(
        protected WithdrawRequestRepositoryInterface $withdrawRequestRepository,
        protected TransactionRepositoryInterface $transactionRepository,
    ) { }

    public function handle(Order $order, Transaction $transaction, array $extra = [])
    {
        if ($order->itemType() == WithdrawRequest::ENTITY_TYPE) {
            $this->handleRequest($order, $transaction);
            return;
        }

        $this->handleItemTransaction($order, $transaction, $extra);
    }

    private function handleRequest(Order $order, Transaction $transaction): void
    {
        try {
            $this->withdrawRequestRepository->updateSuccessPayment($order, $transaction);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }
    }

    private function handleItemTransaction(Order $order, Transaction $transaction, array $extra =[]): void
    {
        /**
         * @var Gateway $gateway
         */
        $gateway = Gateway::query()
            ->where('id', $transaction->gateway_id)
            ->first();

        if (null === $gateway) {
            return;
        }

        /*
         * Cover for old version of Activity Point app.
         * No support Activity Point because Points directly move to owner
         */
        if ($gateway->service == 'activitypoint') {
            return;
        }

        try {
            if (!$gateway->getService()->hasAccessViaFilterMode(Transaction::ENTITY_TYPE)) {
                return;
            }

            $this->transactionRepository->createIncomingTransaction($transaction, $extra);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }
    }
}
