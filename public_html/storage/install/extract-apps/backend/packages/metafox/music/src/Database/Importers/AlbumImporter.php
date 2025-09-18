<?php

namespace MetaFox\Music\Database\Importers;

use MetaFox\Music\Models\AlbumPrivacyStream;
use MetaFox\Music\Models\AlbumText;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Music\Models\Album as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class AlbumImporter extends JsonImporter
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
        $this->processPrivacyStream(AlbumPrivacyStream::class);
    }

    public function processImport()
    {
        $this->remapRefs([
            '$owner', '$user',
            '$image.$id' => ['image_file_id'],
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(AlbumText::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'              => $oid,
                'privacy'         => $this->privacyMapEntry($entry),
                'view_id'         => $entry['view_id'] ?? 0,
                'user_id'         => $entry['user_id'] ?? null,
                'user_type'       => $entry['user_type'] ?? null,
                'owner_id'        => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'      => $entry['owner_type'] ?? $entry['user_type'],
                'total_track'     => $entry['total_track'] ?? 0,
                'total_length'    => $entry['total_length'] ?? 0,
                'total_like'      => $entry['total_like'] ?? 0,
                'total_comment'   => $entry['total_comment'] ?? 0,
                'total_reply'     => $entry['total_reply'] ?? 0,
                'total_play'      => $entry['total_play'] ?? 0,
                'total_rating'    => $entry['total_rating'] ?? 0,
                'total_score'     => $entry['total_score'] ?? 0,
                'image_file_id'   => $entry['image_file_id'] ?? null,
                'name'            => html_entity_decode($entry['name'] ?? ''),
                'year'            => $entry['year'] ?? null,
                'module_id'       => $entry['module_id'] ?? null,
                'package_id'      => $entry['package_id'] ?? null,
                'created_at'      => $entry['created_at'] ?? null,
                'updated_at'      => $entry['updated_at'] ?? null,
                'album_type'      => $entry['album_type'] ?? 0,
                'is_featured'     => $entry['is_featured'] ?? 0,
                'is_sponsor'      => $entry['is_sponsor'] ?? 0,
                'featured_at'     => $entry['featured_at'] ?? null,
                'sponsor_in_feed' => $entry['sponsor_in_feed'] ?? 0,
            ]
        );

        $this->addEntryToBatch(AlbumText::class, [
            'id'          => $entry['$oid'],
            'text'        => $entry['text'] ?? '',
            'text_parsed' => $this->parseText($entry['text_parsed'] ?? ''),
        ]);
    }
}
