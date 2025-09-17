<?php

namespace MetaFox\Chat\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Chat\Models\Subscription as Model;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\User;

/*
 * stub: packages/database/json-importer.stub
 */

class SubscriptionImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'anotherUser_id', 'room_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user',
            '$anotherUser',
            '$room',
        ]);
        $this->mapSubscriptionName();

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'           => $entry['$oid'],
            'room_id'      => $entry['room_id'],
            'name'         => $entry['name'] ?? '',
            'is_favourite' => $entry['is_favourite'] ?? 0,
            'is_showed'    => $entry['is_showed'] ?? 1,
            'is_deleted'   => $entry['is_deleted'] ?? 0,
            'user_id'      => $entry['user_id'],
            'user_type'    => $entry['user_type'],
            'total_unseen' => $entry['total_unseen'] ?? 0,
            'created_at'   => $entry['created_at'] ?? now(),
        ]);
    }

    public function mapSubscriptionName()
    {
        $others = $this->pickEntriesValue('anotherUser_id');

        $rows = User::query()->whereIn('id', $others)
            ->pluck('full_name', 'id')
            ->toArray();
        foreach ($this->entries as &$entry) {
            $key = Arr::get($entry, 'anotherUser_id');
            if (!isset($rows[$key])) {
                $entry['name'] = $entry['room_name'] ?? '';
                continue;
            }
            $entry['name'] = $rows[$key];
        }
    }
}
