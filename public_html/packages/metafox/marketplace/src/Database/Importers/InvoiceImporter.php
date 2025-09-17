<?php

namespace MetaFox\Marketplace\Database\Importers;

use MetaFox\Marketplace\Models\Invoice as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class InvoiceImporter extends JsonImporter
{
    protected array $requiredColumns = [
        'user_id',
        'user_type',
        'listing_id',
        'currency_id',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$user', '$listing']);
        $this->remapCurrency();
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'              => $entry['$oid'],
            'user_id'         => $entry['user_id'],
            'user_type'       => $entry['user_type'],
            'listing_id'      => $entry['listing_id'],
            'currency_id'     => $entry['currency_id'],
            'price'           => $entry['price'] ?? 0,
            'payment_gateway' => $entry['payment_gateway'] ?? 0,
            'status'          => $entry['status'] ?? 'init',
            'paid_at'         => $entry['paid_at'] ?? null,
            'created_at'      => $entry['created_at'] ?? null,
            'updated_at'      => $entry['updated_at'] ?? null,
        ]);
    }
}
