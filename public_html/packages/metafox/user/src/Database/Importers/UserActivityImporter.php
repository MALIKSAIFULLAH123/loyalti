<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserActivity as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserActivityImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id'];

    public function beforePrepare(): void
    {
        $this->remapRefs([
            '$user' => ['$oid'],
        ]);
    }

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user',
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'              => $entry['user_id'],
            'last_login'      => $entry['last_login'] ?? null,
            'last_activity'   => $entry['last_activity'] ?? null,
            'last_ip_address' => $entry['last_ip_address'] ?? null,
        ]);
    }
}
