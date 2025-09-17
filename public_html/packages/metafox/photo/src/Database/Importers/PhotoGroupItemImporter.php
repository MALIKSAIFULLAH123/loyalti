<?php

namespace MetaFox\Photo\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Photo\Models\PhotoGroupItem as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PhotoGroupItemImporter extends JsonImporter
{
    protected array $requiredColumns = [
        'group_id', 'item_id', 'item_type',
    ];

    // fill from data to model attributes.
    public function processImport()
    {
        $this->remapRefs(['$group', '$item']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'group_id'   => $entry['group_id'] ?? null,
            'item_id'    => $entry['item_id'] ?? null,
            'item_type'  => $entry['item_type'] ?? null,
            'ordering'   => $entry['ordering'] ?? 0,
            'created_at' => $entry['created_at'] ?? null,
            'updated_at' => $entry['updated_at'] ?? null,
        ]);
    }
}
