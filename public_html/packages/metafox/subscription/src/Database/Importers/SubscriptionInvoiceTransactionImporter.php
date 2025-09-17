<?php

namespace MetaFox\Subscription\Database\Importers;

use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Support\Payment;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Subscription\Models\SubscriptionInvoiceTransaction as Model;
use MetaFox\Subscription\Support\Helper;

/*
 * stub: packages/database/json-importer.stub
 */

class SubscriptionInvoiceTransactionImporter extends JsonImporter
{
    protected array $requiredColumns = ['invoice_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$invoice']);

        $this->remapCurrency();

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'              => $entry['$oid'] ?? null,
            'invoice_id'      => $entry['invoice_id'] ?? null,
            'currency'        => $entry['currency_id'] ?? null,
            'transaction_id'  => $entry['transaction_id'] ?? null,
            'payment_gateway' => $entry['payment_gateway'] ?? 0,
            'paid_price'      => $entry['paid_price'] ?? 0,
            'payment_status'  => $this->handlePaymentStatus($entry['payment_status'] ?? null),
            'payment_type'    => $this->handlePaymentType($entry['payment_type'] ?? null),
            'created_at'      => $entry['created_at'] ?? null,
        ]);
    }

    private function handlePaymentStatus(?string $status): ?string
    {
        $statusList = Helper::getPaymentStatus();

        if ($status && in_array($status, $statusList)) {
            return $status;
        }

        return Order::STATUS_INIT;
    }

    private function handlePaymentType(?string $type): string
    {
        $typeList = Helper::getPaymentType();

        if ($type && in_array($type, $typeList)) {
            return $type;
        }

        return Payment::PAYMENT_ONETIME;
    }
}
