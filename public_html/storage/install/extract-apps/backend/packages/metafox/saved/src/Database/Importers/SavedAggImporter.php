<?php

namespace MetaFox\Saved\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Saved\Models\SavedAgg as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class SavedAggImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'item_type'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'          => $entry['$oid'],
            'user_id'     => $entry['user_id'] ?? null,
            'user_type'   => $entry['user_type'] ?? null,
            'item_type'   => $entry['item_type'] ?? null,
            'total_saved' => $entry['total_saved'] ?? 1,
        ]);
    }
}
