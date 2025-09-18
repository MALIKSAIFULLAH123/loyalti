<?php

namespace MetaFox\Subscription\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Subscription\Models\SubscriptionCancelReason as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class SubscriptionCancelReasonImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'             => $entry['$oid'] ?? null,
            'is_default'     => $entry['is_default'] ?? false,
            'total_canceled' => $entry['total_canceled'] ?? 0,
            'title'          => $entry['title'] ?? '',
            'status'         => $entry['status'] ?? '',
            'ordering'       => $entry['ordering'] ?? 0,
            'created_at'     => $entry['created_at'] ?? null,
            'updated_at'     => $entry['updated_at'] ?? null,
        ]);
    }
}
