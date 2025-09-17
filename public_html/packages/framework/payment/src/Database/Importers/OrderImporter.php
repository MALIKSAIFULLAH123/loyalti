<?php

namespace MetaFox\Payment\Database\Importers;

use MetaFox\Payment\Models\Order as Model;
use MetaFox\Platform\Support\JsonImporter;

class OrderImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    protected array $requiredColumns = [
        'item_id', 'item_type',
        'user_id', 'user_type',
        'currency_id',
    ];

    public function processImport()
    {
        $this->remapRefs(['$item', '$gateway', '$user']);

        $this->remapCurrency();

        $this->remapStatus();

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'               => $entry['$oid'],
            'currency'         => $entry['currency_id'],
            'item_id'          => $entry['item_id'],
            'item_type'        => $entry['item_type'],
            'user_id'          => $entry['user_id'],
            'user_type'        => $entry['user_type'],
            'title'            => $entry['title'],
            'total'            => $entry['total'],
            'payment_type'     => $entry['payment_type'],
            'status'           => $entry['status'],
            'recurring_status' => $entry['recurring_status'],
            'created_at'       => $entry['created_at'] ?? null,
            'updated_at'       => $entry['updated_at'] ?? null,
            'gateway_id'       => $entry['gateway_id'] ?? $entry['default_gateway'],
        ]);
    }

    private function remapStatus(): void
    {
        foreach ($this->entries as &$entry) {
            $status = Model::STATUS_FAILED;

            switch ($entry['status']) {
                case 1:
                    $status = Model::STATUS_PENDING_APPROVAL;
                    break;
                case 2:
                    $status = Model::STATUS_COMPLETED;
                    break;
            }

            $entry['status'] = $status;
        }
    }
}
