<?php

namespace MetaFox\ActivityPoint\Database\Importers;

use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Payment\Models\Gateway;
use MetaFox\ActivityPoint\Models\PackagePurchase as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PackagePurchaseImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'user_type', 'package_id', 'currency_id'];

    private int $defaultPayment;

    public function __construct()
    {
        $payment = Gateway::query()->where('is_active', 1)->first();

        $this->defaultPayment = $payment instanceof Gateway ? $payment->id : 0;
    }

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->processPaymentOrder();
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$gateway', '$package',
        ]);

        $this->remapCurrency();

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(Model::class, [
            'id'          => $oid,
            'package_id'  => $entry['package_id'] ?? null,
            'status'      => $entry['status'] ?? 0,
            'currency_id' => $entry['currency_id'] ?? null,
            'price'       => $entry['price'] ?? 0,
            'gateway_id'  => $entry['gateway_id'] ?? $this->defaultPayment,
            'points'      => $entry['points'] ?? 0,
            'user_id'     => $entry['user_id'] ?? null,
            'user_type'   => $entry['user_type'] ?? null,
            'updated_at'  => $entry['updated_at'] ?? null,
            'created_at'  => $entry['created_at'] ?? null,
        ]);
    }

    private function processPaymentOrder(): void
    {
        try {
            $data = [];
            foreach ($this->entries as &$entry) {
                $data[] = [
                    '$id'              => 'po.' . $entry['$id'],
                    '$gateway'         => $entry['$gateway'],
                    'default_gateway'  => $this->defaultPayment,
                    '$user'            => $entry['$user'],
                    '$item'            => $entry['$id'],
                    'title'            => $entry['title'],
                    'total'            => $entry['price'],
                    '$currency'        => $entry['$currency'],
                    'payment_type'     => 'onetime',
                    'status'           => $entry['status'],
                    'recurring_status' => 'unset',
                    'updated_at'       => $entry['updated_at'] ?? null,
                    'created_at'       => $entry['created_at'] ?? null,
                ];
            }

            $this->exportBundledEntries($data, Order::ENTITY_TYPE, 43);
        } catch (\Exception $e) {
            $this->error(sprintf('%s:%s', __METHOD__, $e->getMessage()));
        }
    }
}
