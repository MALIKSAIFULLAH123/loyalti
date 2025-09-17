<?php

namespace MetaFox\Comment\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Comment\Models\CommentHistory as Model;
use MetaFox\Comment\Models\CommentAttachment;

/*
 * stub: packages/database/json-importer.stub
 */

class CommentHistoryImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'user_type', 'comment_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$comment', '$item',
        ]);

        $this->remapEmoji('content');

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'              => $entry['$oid'] ?? null,
            'user_id'         => $entry['user_id'] ?? null,
            'user_type'       => $entry['user_type'] ?? null,
            'comment_id'      => $entry['comment_id'] ?? null,
            'item_id'         => $entry['item_id'] ?? 0,
            'item_type'       => $this->handleItemType($entry['item_type'] ?? 'storage_file'),
            'content'         => isset($entry['content']) ? $this->parseText($entry['content'], false, true) : null,
            'params'          => $entry['params'] ?? null,
            'phrase'          => $entry['phrase'] ?? null,
            'created_at'      => $entry['created_at'] ?? null,
            'updated_at'      => $entry['updated_at'] ?? null,
            'tagged_user_ids' => $this->handleTaggedUser($entry),
        ]);
    }

    private function handleItemType(?string $itemType): string
    {
        $itemTypeList = [
            CommentAttachment::TYPE_FILE,
            CommentAttachment::TYPE_STICKER,
            CommentAttachment::TYPE_LINK,
        ];

        if ($itemType && in_array($itemType, $itemTypeList)) {
            return $itemType;
        }

        return CommentAttachment::TYPE_FILE;
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
}
