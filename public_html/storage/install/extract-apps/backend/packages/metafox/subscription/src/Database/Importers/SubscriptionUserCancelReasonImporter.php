<?php

namespace MetaFox\Subscription\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Subscription\Models\SubscriptionUserCancelReason as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class SubscriptionUserCancelReasonImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$invoice', '$reason']);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'] ?? null,
            'invoice_id' => $entry['invoice_id'] ?? null,
            'reason_id'  => $entry['reason_id'] ?? null,
            'created_at' => $entry['created_at'] ?? null,
        ]);
    }
}
