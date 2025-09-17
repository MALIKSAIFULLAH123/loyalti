<?php

namespace MetaFox\Photo\Database\Importers;

use MetaFox\Photo\Models\CollectionStatistic as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class CollectionStatisticImporter extends JsonImporter
{
    protected array $requiredColumns = ['item_id', 'item_type'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$item' => ['item_id', 'item_type']]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'          => $entry['$oid'],
            'total_video' => $entry['total_video'] ?? 0,
            'total_photo' => $entry['total_photo'] ?? 0,
            'item_id'     => $entry['item_id'] ?? null,
            'item_type'   => $entry['item_type'] ?? null,
        ]);
    }
}
