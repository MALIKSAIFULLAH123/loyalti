<?php

namespace MetaFox\Friend\Database\Importers;

use MetaFox\Friend\Models\FriendRequest as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class FriendRequestImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'owner_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$user', '$owner']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'user_id'    => $entry['user_id'],
            'owner_id'   => $entry['owner_id'],
            'user_type'  => $entry['user_type'],
            'owner_type' => $entry['owner_type'],
            'status_id'  => $entry['status_id'] ?? null,
            'is_deny'    => $entry['is_deny'] ?? null,
            'created_at' => $entry['created_at'] ?? now(),
            'updated_at' => $entry['updated_at'] ?? null,
        ]);
    }
}
