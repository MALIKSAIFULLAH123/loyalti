<?php

namespace MetaFox\Saved\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Saved\Models\SavedListData as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class SavedListDataImporter extends JsonImporter
{
    protected array $requiredColumns = ['list_id', 'saved_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$list', '$saved',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'       => $entry['$oid'],
            'list_id'  => $entry['list_id'] ?? null,
            'saved_id' => $entry['saved_id'] ?? null,
        ]);
    }
}
