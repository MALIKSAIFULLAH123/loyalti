<?php

namespace MetaFox\Comment\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Comment\Models\Comment as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class CommentImporter extends JsonImporter
{
    protected array $requiredColumns = [
        'user_id', 'owner_id', 'item_id',
        'user_type', 'owner_type', 'item_type',
    ];

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$owner', '$item', '$parent',
        ]);

        $this->remapEmoji();

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'              => $entry['$oid'] ?? null,
            'user_id'         => $entry['user_id'] ?? null,
            'user_type'       => $entry['user_type'] ?? null,
            'owner_id'        => $entry['owner_id'] ?? $entry['user_id'],
            'owner_type'      => $entry['owner_type'] ?? $entry['user_type'],
            'item_id'         => $entry['item_id'] ?? null,
            'item_type'       => $entry['item_type'] ?? null,
            'module_id'       => $entry['module_id'] ?? null,
            'package_id'      => $entry['package_id'] ?? null,
            'parent_id'       => $entry['parent_id'] ?? 0,
            'is_approved'     => $entry['is_approved'] ?? 1,
            'is_spam'         => $entry['is_spam'] ?? 0,
            'total_like'      => $entry['total_like'] ?? 0,
            'total_comment'   => $entry['total_comment'] ?? 0,
            'text'            => $this->parseMention($entry['text'] ?? '', $entry),
            'text_parsed'     => $this->parseText($entry['text_parsed'] ?? '', false, true, $entry),
            'updated_at'      => $entry['updated_at'] ?? null,
            'created_at'      => $entry['created_at'] ?? null,
            'tagged_user_ids' => $this->handleTaggedUser($entry),
        ]);
    }

    private function handleTaggedUser(array $data): ?string
    {
        $usersRef = Arr::get($data, 'tagged_user_ids', []);
        if (empty($usersRef)) {
            return null;
        }

        $userIds = $this->getEntryRepository()
            ->getModel()
            ->newQuery()
            ->whereIn('ref_id', $usersRef)
            ->get('resource_id')
            ->whereNotNull('resource_id')
            ->toArray();

        $userIds = array_map(function ($item) {
            return $item['resource_id'];
        }, $userIds);

        return json_encode(array_values($userIds));
    }

    public function afterImport(): void
    {
        $this->processImportUserMention();
    }
}
