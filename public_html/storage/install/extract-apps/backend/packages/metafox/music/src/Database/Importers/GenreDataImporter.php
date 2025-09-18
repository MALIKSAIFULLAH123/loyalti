<?php

namespace MetaFox\Music\Database\Importers;

use MetaFox\Music\Models\GenreData as Model;
use MetaFox\Platform\Support\JsonImporterForCategoryData;

/*
 * stub: packages/database/json-importer.stub
 */

class GenreDataImporter extends JsonImporterForCategoryData
{
    protected array $requiredColumns = ['item_id', 'genre_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$item', '$genre']);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id'], 500);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch($this->getModelClass(), [
            'id'        => $entry['$oid'],
            'item_id'   => $entry['item_id'],
            'item_type' => $entry['item_type'] ?? 'music_song',
            'genre_id'  => $entry['genre_id'],
        ]);
    }
}
