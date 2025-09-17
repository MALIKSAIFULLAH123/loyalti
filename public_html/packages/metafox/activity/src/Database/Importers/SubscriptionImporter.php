<?php

namespace MetaFox\Activity\Database\Importers;

use MetaFox\Activity\Models\Subscription as Model;
use MetaFox\Platform\Support\JsonImporter;

class SubscriptionImporter extends JsonImporter
{
    protected array $modelClass = [];

    public function getModelClass(): string
    {
        return Model::class;
    }

    protected array $requiredColumns = ['user_id', 'owner_id'];

    public function processImport()
    {
        $this->remapRefs([
            '$user',
            '$owner',
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked($this->getModelClass(), ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];
        $this->addEntryToBatch($this->getModelClass(), [
            'id'           => $oid,
            'user_id'      => $entry['user_id'],
            'owner_id'     => $entry['owner_id'],
            'is_active'    => $entry['is_active'] ?? true,
            'special_type' => $entry['special_type'] ?? null,
        ]);
    }
}
