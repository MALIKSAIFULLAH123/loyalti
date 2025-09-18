<?php

namespace MetaFox\LiveStreaming\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Importer\Models\Entry;
use MetaFox\LiveStreaming\Models\LiveVideoPrivacyStream;
use MetaFox\LiveStreaming\Models\LiveVideoTagData;
use MetaFox\LiveStreaming\Models\LiveVideoText;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class LiveVideoImporter extends JsonImporter
{
    protected bool $priorityKeepOldIdSetting = true;

    protected array $requiredColumns = ['user_id', 'owner_id', 'stream_key', 'live_stream_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$image');
        $this->processPrivacyStream(LiveVideoPrivacyStream::class);
    }

    public function processImport()
    {
        $this->remapRefs([
            '$owner', '$user',
            '$image.$id' => ['image_file_id'],
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(LiveVideoText::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'                 => $oid,
                'title'              => html_entity_decode($entry['title'] ?? ''),
                'stream_key'         => $entry['stream_key'],
                'duration'           => $entry['duration'] ?? null,
                'asset_id'           => $entry['asset_id'] ?? null,
                'live_stream_id'     => $entry['live_stream_id'] ?? null,
                'live_type'          => $entry['live_type'] ?? 'mobile',
                'is_streaming'       => $entry['is_streaming'] ?? 0,
                'is_landscape'       => $entry['is_landscape'] ?? 1,
                'module_id'          => $entry['module_id'] ?? Model::ENTITY_TYPE,
                'package_id'         => $entry['package_id'] ?? null,
                'user_id'            => $entry['user_id'] ?? null,
                'user_type'          => $entry['user_type'] ?? null,
                'owner_id'           => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'         => $entry['owner_type'] ?? $entry['user_type'],
                'privacy'            => $this->privacyMapEntry($entry),
                'is_featured'        => $entry['is_featured'] ?? 0,
                'featured_at'        => $entry['featured_at'] ?? null,
                'is_sponsor'         => $entry['is_sponsor'] ?? 0,
                'sponsor_in_feed'    => $entry['sponsor_in_feed'] ?? 0,
                'tags'               => json_encode($entry['tags'] ?? []),
                'total_comment'      => $entry['total_comment'] ?? 0,
                'total_reply'        => $entry['total_reply'] ?? 0,
                'total_like'         => $entry['total_like'] ?? 0,
                'total_share'        => $entry['total_share'] ?? 0,
                'total_view'         => $entry['total_view'] ?? 0,
                'total_attachment'   => $entry['total_attachment'] ?? 0,
                'image_file_id'      => $entry['image_file_id'] ?? null,
                'is_approved'        => $entry['is_approved'] ?? 1,
                'tagged_friends'     => isset($entry['tag_friends']) ? json_encode($entry['tag_friends']) : null,
                'location_latitude'  => $entry['location_latitude'] ?? null,
                'location_longitude' => $entry['location_longitude'] ?? null,
                'location_name'      => isset($entry['location_name']) ? html_entity_decode($entry['location_name']) : null,
                'view_id'            => $entry['view_id'] ?? 0,
                'last_ping'          => $entry['last_ping'] ?? null,
                'total_viewer'       => $entry['total_viewer'] ?? 0,
                'allow_feed'         => $entry['allow_feed'] ?? 0,
                'updated_at'         => $entry['updated_at'] ?? null,
                'created_at'         => $entry['created_at'] ?? null,
            ]
        );

        $this->addEntryToBatch(
            LiveVideoText::class,
            [
                'id'          => $oid,
                'text'        => $entry['text'] ?? '',
                'text_parsed' => $this->parseText($entry['text_parsed'] ?? '', false),
            ]
        );
    }

    public function beforeImport(): void
    {
        foreach ($this->entries as &$entry) {
            $tagFriends = json_decode(Arr::get($entry, 'tag_friends')) ?? [];

            if (empty($tagFriends)) {
                continue;
            }

            $this->handleTagFriend($entry, $tagFriends);
        }
    }

    private function handleTagFriend(array &$entry, array $tagFriends): void
    {
        $userIds = [];

        foreach ($tagFriends as $tagFriend) {
            $userEntry = $this->getEntryRepository()
                ->getEntry($tagFriend, $this->bundle->source);

            if (!$userEntry instanceof Entry) {
                continue;
            }

            $userIds[] = $userEntry->resource_id;
        }

        $entry['tag_friends'] = $userIds;
    }

    public function afterImport(): void
    {
        $this->importTagData(LiveVideoTagData::class);
    }
}
