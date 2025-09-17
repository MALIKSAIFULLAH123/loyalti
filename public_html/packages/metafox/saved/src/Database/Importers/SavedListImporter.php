<?php

namespace MetaFox\Saved\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Saved\Models\SavedList as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class SavedListImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id'];

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
            'id'         => $entry['$oid'],
            'user_id'    => $entry['user_id'] ?? null,
            'user_type'  => $entry['user_type'] ?? null,
            'name'       => html_entity_decode($entry['name'] ?? ''),
            'saved_id'   => $entry['saved_id'] ?? 0,
            'updated_at' => $entry['updated_at'] ?? null,
            'created_at' => $entry['created_at'] ?? null,
            'privacy'    => $this->privacyMapEntry($entry),
        ]);
    }
}
