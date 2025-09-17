<?php

namespace MetaFox\Photo\Database\Importers;

use MetaFox\Photo\Models\AlbumText;
use MetaFox\Photo\Models\AlbumPrivacyStream;
use MetaFox\Photo\Models\PhotoAlbumTagData;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Photo\Models\Album as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class AlbumImporter extends JsonImporter
{
    protected bool $priorityKeepOldIdSetting = true;

    protected array $requiredColumns = ['user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->processPrivacyStream(AlbumPrivacyStream::class);
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$owner', '$cover_photo' => ['cover_photo_id'],
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
                'name'            => html_entity_decode($entry['name'] ?? ''),
                'module_id'       => $entry['module_id'] ?? Model::ENTITY_TYPE,
                'privacy'         => $this->privacyMapEntry($entry),
                'is_featured'     => $entry['is_featured'] ?? 0,
                'is_sponsor'      => $entry['is_sponsor'] ?? 0,
                'is_approved'     => $entry['is_approved'] ?? 1,
                'user_id'         => $entry['user_id'] ?? null,
                'user_type'       => $entry['user_type'] ?? null,
                'owner_id'        => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'      => $entry['owner_type'] ?? $entry['user_type'],
                'album_type'      => $entry['album_type'] ?? 0,
                'cover_photo_id'  => $entry['cover_photo_id'] ?? 0,
                'sponsor_in_feed' => $entry['sponsor_in_feed'] ?? 0,
                'updated_at'      => $entry['updated_at'] ?? null,
                'created_at'      => $entry['created_at'] ?? null,
                'deleted_at'      => $entry['deleted_at'] ?? null,
                'total_photo'     => $entry['total_photo'] ?? 0,
                'total_like'      => $entry['total_like'] ?? 0,
                'total_item'      => $entry['total_item'] ?? 0,
                'total_share'     => $entry['total_share'] ?? 0,
                'total_comment'   => $entry['total_comment'] ?? 0,
                'total_reply'     => $entry['total_reply'] ?? 0,
                'total_view'      => $entry['total_view'] ?? 0,
                'featured_at'     => $entry['featured_at'] ?? null,
            ]
        );

        $this->addEntryToBatch(
            AlbumText::class,
            [
                'id'          => $oid,
                'text'        => $entry['description'] ?? '',
                'text_parsed' => $this->parseText($entry['description'] ?? ''),
            ]
        );
    }

    public function afterImport(): void
    {
        $this->importTagData(PhotoAlbumTagData::class);
    }
}
