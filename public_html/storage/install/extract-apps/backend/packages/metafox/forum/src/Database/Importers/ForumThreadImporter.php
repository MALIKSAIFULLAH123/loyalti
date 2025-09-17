<?php

namespace MetaFox\Forum\Database\Importers;

use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Models\ForumThread as Model;
use MetaFox\Forum\Models\ForumThreadTagData;
use MetaFox\Forum\Models\ForumThreadText;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class ForumThreadImporter extends JsonImporter
{
    protected bool $keepOldId = true;

    protected array $requiredColumns = ['user_id', 'forum_id', 'owner_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$forum',
            '$user',
            '$owner',
            '$item',
        ]);

        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(ForumThread::class, ['id']);
        $this->upsertBatchEntriesInChunked(ForumThreadText::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $id = $entry['$oid'];

        $this->addEntryToBatch(ForumThread::class, [
            'id'               => $id,
            'title'            => htmlspecialchars_decode($entry['title'] ?? '', ENT_QUOTES),
            'is_wiki'          => $entry['is_wiki'] ?? false,
            'tags'             => json_encode($entry['tags'] ?? null),
            'is_approved'      => $entry['is_approved'] ?? true,
            'is_sticked'       => $entry['is_sticked'] ?? false,
            'is_closed'        => $entry['is_closed'] ?? false,
            'is_sponsor'       => $entry['is_sponsor'] ?? false,
            'sponsor_in_feed'  => $entry['sponsor_in_feed'] ?? false,
            'total_attachment' => $entry['total_attachment'] ?? 0,
            'total_comment'    => $entry['total_comment'] ?? 0,
            'total_view'       => $entry['total_view'] ?? 0,
            'total_like'       => $entry['total_like'] ?? 0,
            'total_share'      => $entry['total_share'] ?? 0,
            'created_at'       => $entry['created_at'] ?? null,
            'updated_at'       => $entry['updated_at'] ?? null,
            'forum_id'         => $entry['forum_id'] ?? 0,
            'user_id'          => $entry['user_id'] ?? null,
            'user_type'        => $entry['user_type'] ?? null,
            'owner_id'         => $entry['owner_id'] ?? null,
            'owner_type'       => $entry['owner_type'] ?? null,
            'item_id'          => $entry['item_id'] ?? 0,
            'item_type'        => $entry['item_type'] ?? null,
        ]);

        $this->addEntryToBatch(ForumThreadText::class, [
            'id'          => $id,
            'text'        => $entry['text'] ?? '',
            'text_parsed' => $this->parseText($entry['text_parsed'] ?? ''),
        ]);
    }

    public function afterImport(): void
    {
        $this->importTagData(ForumThreadTagData::class);
    }
}
