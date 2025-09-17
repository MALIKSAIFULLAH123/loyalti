<?php

namespace MetaFox\Core\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Core\Models\Privacy as Model;
use MetaFox\Platform\Support\JsonImporter;

class PrivacyImporter extends JsonImporter
{
    protected array $requiredColumns = ['privacy', 'privacy_type', 'user_id', 'owner_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function beforePrepare(): void
    {
        $this->remapRefs([
            '$owner' => ['owner_id', 'owner_type'],
        ]);

        $ownerIds     = $this->pickEntriesValue('owner_id');
        $ownerTypes   = $this->pickEntriesValue('owner_type');
        $privacyTypes = $this->pickEntriesValue('privacy_type');
        $privacyType  = array_shift($privacyTypes);
        $ownerType    = array_shift($ownerTypes);
        if ($privacyType == 'user_friend_list') {
            return;
        }
        $result = Model::query()
            ->whereIn('owner_id', $ownerIds)
            ->where('owner_type', $ownerType)
            ->where('privacy_type', $privacyType)
            ->distinct('owner_id')
            ->pluck('privacy_id', 'owner_id')
            ->toArray();
        foreach ($this->entries as &$entry) {
            if (!Arr::exists($result, $entry['owner_id'])) {
                continue;
            }
            $entry['$oid'] = $result[$entry['owner_id']];
        }
    }

    // batch to raw query in database.
    public function processImport()
    {
        $this->remapRefs([
            '$owner' => ['owner_id', 'owner_type'],
            '$user'  => ['user_id', 'user_type'],
            '$item'  => ['item_id', 'item_type'],
        ]);

        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['privacy_id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(Model::class, [
            'privacy_id'   => $oid,
            'item_id'      => $entry['item_id'],
            'item_type'    => $entry['item_type'],
            'owner_id'     => $entry['owner_id'],
            'owner_type'   => $entry['owner_type'],
            'privacy'      => $entry['privacy'],
            'privacy_type' => $entry['privacy_type'],
            'user_id'      => $entry['user_id'],
            'user_type'    => $entry['user_type'],
        ]);
    }
}
