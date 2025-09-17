<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserBlocked as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserBlockedImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'created_at',
        'updated_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'user',
        'owner',
    ];

    protected array $requiredColumns = [
        'user_id',
        'owner_id',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$owner', '$user']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'created_at' => $entry['created_at'] ?? now(),
            'updated_at' => $entry['updated_at'] ?? null,
            'user_id'    => $entry['user_id'] ?? null,
            'owner_id'   => $entry['owner_id'] ?? null,
            'user_type'  => $entry['user_type'] ?? null,
            'owner_type' => $entry['owner_type'] ?? null,
        ]);
    }
}
