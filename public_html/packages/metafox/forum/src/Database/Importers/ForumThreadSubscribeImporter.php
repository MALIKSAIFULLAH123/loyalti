<?php

namespace MetaFox\Forum\Database\Importers;

use MetaFox\Forum\Models\ForumThreadSubscribe as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class ForumThreadSubscribeImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'created_at',
        'updated_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'item',
        'user',
    ];

    protected array $requiredColumns = [
        'user_id', 'item_id',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$item' => ['item_id'], '$user' => ['user_id', 'user_type'],]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'user_id'    => $entry['user_id'] ?? null,
            'user_type'  => $entry['user_type'] ?? null,
            'item_id'    => $entry['item_id'] ?? null,
            'created_at' => $entry['created_at'] ?? now(),
            'updated_at' => $entry['updated_at'] ?? null,
        ]);
    }
}
