<?php

namespace MetaFox\LiveStreaming\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\LiveStreaming\Models\NotificationSetting as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class NotificationSettingImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'owner_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$owner',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'user_id'    => $entry['user_id'] ?? null,
            'user_type'  => $entry['user_type'] ?? null,
            'owner_id'   => $entry['owner_id'] ?? $entry['user_id'],
            'owner_type' => $entry['owner_type'] ?? $entry['user_type'],
            'updated_at' => $entry['updated_at'] ?? null,
            'created_at' => $entry['created_at'] ?? null,
        ]);
    }
}
