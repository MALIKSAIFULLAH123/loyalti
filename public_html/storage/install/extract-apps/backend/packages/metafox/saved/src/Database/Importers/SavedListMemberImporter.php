<?php

namespace MetaFox\Saved\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Saved\Models\SavedListMember as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class SavedListMemberImporter extends JsonImporter
{
    protected array $requiredColumns = ['list_id', 'user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$list', '$user',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['list_id', 'user_id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'      => $entry['$oid'],
            'list_id' => $entry['list_id'] ?? null,
            'user_id' => $entry['user_id'] ?? null,
        ]);
    }
}
