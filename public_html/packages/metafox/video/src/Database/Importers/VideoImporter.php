<?php

namespace MetaFox\Video\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Video\Models\PrivacyStream;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Models\VideoTagData;
use MetaFox\Video\Models\VideoText;

/*
 * stub: packages/database/json-importer.stub
 */

class VideoImporter extends JsonImporter
{
    protected bool $priorityKeepOldIdSetting = true;

    protected array $requiredColumns = [
        'user_id',
        'owner_id',
        'user_type',
        'owner_type',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$video');
        $this->appendFileBundle('$image');
        $this->appendFileBundle('$thumbnail');
        $this->processPrivacyStream(PrivacyStream::class);
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$owner',
            '$image.$id'     => ['image_file_id'],
            '$video.$id'     => ['video_file_id'],
            '$thumbnail.$id' => ['thumbnail_file_id'],
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(VideoText::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $isFfmpeg = $entry['service_name'] == 'ffmpeg';
        $this->addEntryToBatch(Model::class, [
            'id'                 => $entry['$oid'],
            'in_process'         => $entry['in_process'] ?? 0,
            'is_stream'          => $entry['is_stream'] ?? 0,
            'is_spotlight'       => $entry['is_spotlight'] ?? 0,
            'group_id'           => $entry['group_id'] ?? 0,
            'album_id'           => $entry['album_id'] ?? 0,
            'album_type'         => $entry['album_type'] ?? 0,
            'is_featured'        => $entry['is_featured'] ?? 0,
            'is_sponsor'         => $entry['is_sponsor'] ?? 0,
            'is_approved'        => $entry['is_approved'] ?? 1,
            'featured_at'        => $entry['featured_at'] ?? null,
            'sponsor_in_feed'    => $entry['sponsor_in_feed'] ?? 0,
            'module_id'          => Model::ENTITY_TYPE,
            'owner_id'           => $entry['owner_id'] ?? null,
            'owner_type'         => $entry['owner_type'] ?? null,
            'user_id'            => $entry['user_id'] ?? null,
            'user_type'          => $entry['user_type'] ?? null,
            'privacy'            => $this->privacyMapEntry($entry),
            'video_file_id'      => $entry['video_file_id'] ?? null,
            'image_file_id'      => $entry['image_file_id'] ?? null,
            'thumbnail_file_id'  => $entry['thumbnail_file_id'] ?? null,
            'title'              => html_entity_decode($entry['title'] ?? ''),
            'destination'        => $isFfmpeg ? null : $entry['destination'],
            'thumbnail_path'     => $entry['thumbnail_path'] ?? null,
            'asset_id'           => $entry['asset_id'] ?? null,
            'video_url'          => $entry['video_url'] ?? null,
            'embed_code'         => $entry['embed_code'] ?? null,
            'content'            => $entry['content'] ?? null,
            'file_ext'           => $entry['file_ext'] ?? null,
            'total_like'         => $entry['total_like'] ?? 0,
            'total_share'        => $entry['total_share'] ?? 0,
            'total_comment'      => $entry['total_comment'] ?? 0,
            'total_reply'        => $entry['total_reply'] ?? 0,
            'total_rating'       => $entry['total_rating'] ?? 0,
            'total_score'        => (int) $entry['total_score'] ?? 0,
            'total_view'         => $entry['total_view'] ?? 0,
            'duration'           => $entry['duration'] ?? null,
            'resolution_x'       => $entry['resolution_x'] ?? null,
            'resolution_y'       => $entry['resolution_y'] ?? null,
            'location_latitude'  => $entry['location_latitude'] ?? null,
            'location_longitude' => $entry['location_longitude'] ?? null,
            'location_name'      => isset($entry['location_name']) ? html_entity_decode($entry['location_name']) : null,
            'created_at'         => $entry['created_at'] ?? null,
            'updated_at'         => $entry['updated_at'] ?? null,
        ]);

        $this->addEntryToBatch(VideoText::class, [
            'id'          => $entry['$oid'],
            'text'        => $this->parseMention($entry['text'] ?? '', $entry),
            'text_parsed' => $this->parseText($entry['text_parsed'] ?? '', true, true, $entry),
        ]);
    }

    public function afterImport(): void
    {
        $this->processImportUserMention();
        $this->importTagData(VideoTagData::class);
    }
}
