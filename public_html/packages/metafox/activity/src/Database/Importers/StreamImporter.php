<?php

namespace MetaFox\Activity\Database\Importers;

use MetaFox\Activity\Models\Stream as Model;
use MetaFox\Platform\Support\JsonImporter;

class StreamImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    protected array $requiredColumns = [
        'item_id', 'item_type',
        'user_id', 'user_type',
        'owner_id', 'owner_type',
        'feed_id',
    ];

    // batch to raw query in database.
    public function processImport()
    {
        $this->remapRefs([
            '$item' => ['item_id', 'item_type'],
            '$owner', '$user',
            '$privacy',
        ]);

        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked($this->getModelClass(), ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];
        $this->addEntryToBatch($this->getModelClass(), [
            'id'         => $oid,
            'feed_id'    => $entry['feed_id'],
            'item_id'    => $entry['item_id'],
            'item_type'  => $entry['item_type'],
            'user_id'    => $entry['user_id'],
            'owner_id'   => $entry['owner_id'],
            'owner_type' => $entry['owner_type'],
            'created_at' => $entry['created_at'],
            'updated_at' => $entry['updated_at'],
            'privacy_id' => $entry['privacy_id'] ?? $entry['default_privacy_id'],
        ]);
    }
}
