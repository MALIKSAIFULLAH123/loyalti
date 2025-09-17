<?php

namespace MetaFox\Saved\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Saved\Models\Saved as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class SavedImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'item_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$item',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'user_id'    => $entry['user_id'] ?? null,
            'user_type'  => $entry['user_type'] ?? null,
            'item_id'    => $entry['item_id'] ?? null,
            'item_type'  => $entry['item_type'] ?? null,
            'is_opened'  => $entry['is_opened'] ?? 0,
            'updated_at' => $entry['updated_at'] ?? null,
            'created_at' => $entry['created_at'] ?? null,
        ]);
    }
}
