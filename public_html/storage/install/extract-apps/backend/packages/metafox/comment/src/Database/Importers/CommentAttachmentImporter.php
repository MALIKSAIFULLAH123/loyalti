<?php

namespace MetaFox\Comment\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Comment\Models\CommentAttachment as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class CommentAttachmentImporter extends JsonImporter
{
    protected array $requiredColumns = ['comment_id', 'item_type'];

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$image');
    }

    public function processImport()
    {
        $this->remapRefs([
            '$comment', '$item' => ['item_id'],
            '$image.$id' => ['image_file_id'],
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'] ?? null,
            'comment_id' => $entry['comment_id'] ?? null,
            'item_type'  => $this->handleItemType($entry),
            'item_id'    => $this->handleItem($entry),
            'params'     => $entry['params'] ?? null,
            'deleted_at' => $entry['deleted_at'] ?? null,
        ]);
    }

    private function handleItemType(array $entry): string
    {
        $itemType     = Arr::get($entry, 'item_type');
        $itemTypeList = [Model::TYPE_FILE, Model::TYPE_STICKER, Model::TYPE_LINK];

        if ($itemType && in_array($itemType, $itemTypeList)) {
            return $itemType;
        }

        Arr::set($entry, 'item_type', Model::TYPE_LINK);

        return Model::TYPE_LINK;
    }

    private function handleItem(array $entry): ?int
    {
        $itemId   = null;
        $itemType = $entry['item_type'];

        switch ($itemType) {
            case Model::TYPE_FILE:
                $itemId = $entry['image_file_id'] ?? null;
                break;
            case Model::TYPE_STICKER:
                $itemId = $entry['item_id'] ?? null;
                break;
        }

        return $itemId;
    }
}
