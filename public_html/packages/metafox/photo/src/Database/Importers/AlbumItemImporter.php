<?php

namespace MetaFox\Photo\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Photo\Models\AlbumItem as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class AlbumItemImporter extends JsonImporter
{
    protected array $requiredColumns = ['album_id', 'item_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$album', '$group', '$item',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'         => $oid,
                'ordering'   => $entry['ordering'] ?? 0,
                'created_at' => $entry['created_at'] ?? null,
                'updated_at' => $entry['updated_at'] ?? null,
                'album_id'   => $entry['album_id'] ?? null,
                'item_id'    => $entry['item_id'] ?? null,
                'item_type'  => $entry['item_type'] ?? null,
                'group_id'   => $entry['group_id'] ?? 0,
            ]
        );
    }
}
