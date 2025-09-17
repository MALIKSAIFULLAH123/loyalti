<?php

namespace MetaFox\Music\Database\Importers;

use MetaFox\Music\Models\SongPrivacyStream;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Music\Models\Song as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class SongImporter extends JsonImporter
{
    protected bool $priorityKeepOldIdSetting = true;

    protected array $requiredColumns = ['user_id', 'owner_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$image');
        $this->appendFileBundle('$song');
        $this->processPrivacyStream(SongPrivacyStream::class);
    }

    public function processImport()
    {
        $this->remapRefs([
            '$owner', '$user', '$album', '$genre',
            '$image.$id' => ['image_file_id'],
            '$song.$id'  => ['song_file_id'],
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'              => $oid,
                'view_id'         => $entry['view_id'] ?? 0,
                'explicit'        => $entry['explicit'] ?? false,
                'song_file_id'    => $entry['song_file_id'] ?? null,
                'genre_id'        => $entry['genre_id'] ?? null,
                'album_id'        => $entry['album_id'] ?? null,
                'user_id'         => $entry['user_id'] ?? null,
                'user_type'       => $entry['user_type'] ?? null,
                'owner_id'        => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'      => $entry['owner_type'] ?? $entry['user_type'],
                'module_id'       => $entry['module_id'] ?? null,
                'package_id'      => $entry['package_id'] ?? null,
                'privacy'         => $this->privacyMapEntry($entry),
                'total_track'     => $entry['total_track'] ?? 0,
                'total_length'    => $entry['total_length'] ?? 0,
                'total_like'      => $entry['total_like'] ?? 0,
                'total_comment'   => $entry['total_comment'] ?? 0,
                'total_reply'     => $entry['total_reply'] ?? 0,
                'total_share'     => $entry['total_share'] ?? 0,
                'total_view'      => $entry['total_view'] ?? 0,
                'total_play'      => $entry['total_play'] ?? 0,
                'total_score'     => $entry['total_score'] ?? 0,
                'total_rating'    => $entry['total_rating'] ?? 0,
                'duration'        => $entry['duration'] ?? 0,
                'image_file_id'   => $entry['image_file_id'] ?? null,
                'featured_at'     => $entry['featured_at'] ?? null,
                'is_featured'     => $entry['is_featured'] ?? 0,
                'is_sponsor'      => $entry['is_sponsor'] ?? 0,
                'sponsor_in_feed' => $entry['sponsor_in_feed'] ?? 0,
                'is_approved'     => $entry['is_approved'] ?? 1,
                'name'            => html_entity_decode($entry['name'] ?? ''),
                'description'     => $this->parseText($entry['description'] ?? ''),
                'ordering'        => $entry['ordering'] ?? 0,
                'created_at'      => $entry['created_at'] ?? null,
                'updated_at'      => $entry['updated_at'] ?? null,
            ]
        );
    }
}
