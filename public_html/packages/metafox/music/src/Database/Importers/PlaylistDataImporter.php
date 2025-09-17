<?php

namespace MetaFox\Music\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Music\Models\PlaylistData as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PlaylistDataImporter extends JsonImporter
{
    protected array $requiredColumns = ['item_id', 'playlist_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$item', '$playlist']);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked($this->getModelClass(), ['item_id', 'playlist_id'], 500);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'          => $entry['$oid'],
            'item_id'     => $entry['item_id'],
            'playlist_id' => $entry['playlist_id'],
            'ordering'    => $entry['ordering'] ?? 0,
            'updated_at'  => $entry['updated_at'] ?? null,
            'created_at'  => $entry['created_at'] ?? null,
        ]);
    }
}
