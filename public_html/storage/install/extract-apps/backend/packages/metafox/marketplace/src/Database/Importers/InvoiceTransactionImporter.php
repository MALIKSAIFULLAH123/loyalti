<?php

namespace MetaFox\Marketplace\Database\Importers;

use MetaFox\Marketplace\Models\InvoiceTransaction as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class InvoiceTransactionImporter extends JsonImporter
{
    protected array $requiredColumns = [
        'invoice_id',
        'currency_id',
    ];

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
            'currency_id'     => $entry['currency_id'] ?? null,
            'invoice_id'      => $entry['invoice_id'] ?? null,
            'status'          => $entry['status'] ?? '',
            'price'           => $entry['price'] ?? 0,
            'created_at'      => $entry['created_at'] ?? null,
            'updated_at'      => $entry['updated_at'] ?? null,
            'transaction_id'  => $entry['transaction_id'] ?? null,
            'payment_gateway' => $entry['payment_gateway'] ?? 0,
        ]);
    }
}
