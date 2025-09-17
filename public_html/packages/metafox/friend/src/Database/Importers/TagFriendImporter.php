<?php

namespace MetaFox\Friend\Database\Importers;

use MetaFox\Friend\Models\TagFriend as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class TagFriendImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'owner_id', 'item_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user',
            '$owner',
            '$item',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'user_id'    => $entry['user_id'],
            'user_type'  => $entry['user_type'],
            'owner_id'   => $entry['owner_id'],
            'owner_type' => $entry['owner_type'],
            'item_id'    => $entry['item_id'],
            'item_type'  => $entry['item_type'],
            'px'         => $entry['px'] ?? 0,
            'py'         => $entry['py'] ?? 0,
            'is_mention' => $entry['is_mention'] ?? 0,
            'content'    => $entry['content'] ?? null,
        ]);
    }
}
