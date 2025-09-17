<?php

namespace MetaFox\Like\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Like\Models\LikeAgg as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class LikeAggImporter extends JsonImporter
{
    protected array $requiredColumns = [
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
        $this->remapRefs(['$item', '$reaction']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'             => $entry['$oid'],
            'item_id'        => $entry['item_id'] ?? null,
            'item_type'      => $entry['item_type'] ?? null,
            'reaction_id'    => $entry['reaction_id'] ?? 0,
            'total_reaction' => $entry['total_reaction'] ?? 1,
            'created_at'     => $entry['created_at'] ?? null,
            'updated_at'     => $entry['updated_at'] ?? null,
        ]);
    }
}
