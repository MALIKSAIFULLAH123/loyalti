<?php

namespace MetaFox\Comment\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Comment\Models\CommentHide as Model;
use MetaFox\Comment\Support\Helper;

/*
 * stub: packages/database/json-importer.stub
 */

class CommentHideImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'item_id', 'user_type'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$item',
        ]);

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
            'type'      => $this->handleType($entry['type'] ?? null),
            'is_hidden' => $entry['is_hidden'] ?? true,
        ]);
    }

    private function handleType(?string $type): string
    {
        if ($type && in_array($type, Helper::getHideTypes())) {
            return $type;
        }

        return Helper::HIDE_OWN;
    }
}
