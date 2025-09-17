<?php

namespace MetaFox\Photo\Database\Importers;

use MetaFox\Photo\Models\PhotoGroup as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class PhotoGroupImporter extends JsonImporter
{
    protected array $requiredColumns = [
        'user_id', 'owner_id',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$owner', '$album',
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'                 => $entry['$oid'],
            'total_item'         => $entry['total_item'] ?? null,
            'content'            => $entry['content'] ?? null,
            'privacy'            => $entry['privacy'] ?? 0,
            'total_view'         => $entry['total_view'] ?? 0,
            'total_like'         => $entry['total_like'] ?? 0,
            'total_comment'      => $entry['total_comment'] ?? 0,
            'total_reply'        => $entry['total_reply'] ?? 0,
            'total_share'        => $entry['total_share'] ?? 0,
            'location_name'      => isset($entry['location_name']) ? html_entity_decode($entry['location_name']) : null,
            'location_latitude'  => $entry['location_latitude'] ?? null,
            'location_longitude' => $entry['location_longitude'] ?? null,
            'is_featured'        => $entry['is_featured'] ?? 0,
            'is_sponsor'         => $entry['is_sponsor'] ?? 0,
            'is_approved'        => $entry['is_approved'] ?? 1,
            'featured_at'        => $entry['featured_at'] ?? null,
            'sponsor_in_feed'    => $entry['sponsor_in_feed'] ?? 0,
            'created_at'         => $entry['created_at'] ?? null,
            'updated_at'         => $entry['updated_at'] ?? null,
            'album_id'           => $entry['album_id'] ?? 0,
            'user_id'            => $entry['user_id'] ?? null,
            'user_type'          => $entry['user_type'] ?? null,
            'owner_id'           => $entry['owner_id'] ?? null,
            'owner_type'         => $entry['owner_type'] ?? null,
        ]);
    }
}
