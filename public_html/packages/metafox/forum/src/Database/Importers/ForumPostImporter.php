<?php

namespace MetaFox\Forum\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Forum\Models\ForumPost as Model;
use MetaFox\Forum\Models\ForumPostText;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\User;

/*
 * stub: packages/database/json-importer.stub
 */

class ForumPostImporter extends JsonImporter
{
    protected bool $keepOldId = true;

    protected array $requiredColumns = ['user_id', 'owner_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$thread', '$user', '$owner']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(ForumPostText::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $id = $entry['$oid'];

        $this->addEntryToBatch(Model::class, [
            'id'               => $id,
            'is_approved'      => $entry['is_approved'] ?? true,
            'total_attachment' => $entry['total_attachment'] ?? 0,
            'total_like'       => $entry['total_like'] ?? 0,
            'user_id'          => $entry['user_id'],
            'user_type'        => $entry['user_type'],
            'owner_id'         => $entry['owner_id'],
            'owner_type'       => $entry['owner_type'],
            'thread_id'        => $entry['thread_id'] ?? 0,
            'total_share'      => $entry['total_share'] ?? 0,
            'created_at'       => $entry['created_at'] ?? now(),
            'updated_at'       => $entry['updated_at'] ?? null,
        ]);
        $this->addEntryToBatch(ForumPostText::class, [
            'id'          => $id,
            'text'        => $entry['text'] ?? '',
            'text_parsed' => $this->parseText($entry['text_parsed'] ?? ''),
        ]);
    }
}
