<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserShortcut as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserShortcutImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'item_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$item', '$user']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'        => $entry['$oid'] ?? null,
            'user_id'   => $entry['user_id'] ?? null,
            'user_type' => $entry['user_type'] ?? null,
            'item_id'   => $entry['item_id'] ?? null,
            'item_type' => $entry['item_type'] ?? null,
        ]);
    }
}
