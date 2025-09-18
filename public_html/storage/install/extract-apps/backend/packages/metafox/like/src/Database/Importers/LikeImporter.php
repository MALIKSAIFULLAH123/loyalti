<?php

namespace MetaFox\Like\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Like\Models\Like as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class LikeImporter extends JsonImporter
{
    protected array $requiredColumns = [
        'owner_id',
        'owner_type',
        'user_id',
        'user_type',
        'item_id',
        'item_type',
        'reaction_id',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$user', '$owner', '$item', '$reaction']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'          => $entry['$oid'],
            'owner_id'    => $entry['owner_id'] ?? null,
            'owner_type'  => $entry['owner_type'] ?? null,
            'user_id'     => $entry['user_id'] ?? null,
            'user_type'   => $entry['user_type'] ?? null,
            'item_id'     => $entry['item_id'] ?? null,
            'item_type'   => $entry['item_type'] ?? null,
            'reaction_id' => $entry['reaction_id'] ?? 1,
            'created_at'  => $entry['created_at'] ?? null,
            'updated_at'  => $entry['updated_at'] ?? null,
        ]);
    }
}
