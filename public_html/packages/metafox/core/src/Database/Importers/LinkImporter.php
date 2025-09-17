<?php

namespace MetaFox\Core\Database\Importers;

use MetaFox\Core\Models\PrivacyStream;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Core\Models\Link as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class LinkImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->processPrivacyStream(PrivacyStream::class);
    }

    public function processImport()
    {
        $this->remapRefs([
            '$owner', '$user',
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
                'id'                 => $oid,
                'privacy'            => $this->privacyMapEntry($entry),
                'title'              => empty($entry['title']) ? 'Untitled link' : $entry['title'],
                'is_approved'        => $entry['is_approved'] ?? 1,
                'total_like'         => $entry['total_like'] ?? 0,
                'total_comment'      => $entry['total_comment'] ?? 0,
                'total_reply'        => $entry['total_reply'] ?? 0,
                'total_share'        => $entry['total_share'] ?? 0,
                'updated_at'         => $entry['updated_at'] ?? null,
                'created_at'         => $entry['created_at'] ?? null,
                'description'        => $entry['description'] ?? null,
                'feed_content'       => $entry['feed_content'] ?? null,
                'link'               => $entry['link'] ?? null,
                'host'               => $entry['host'] ?? null,
                'image'              => $entry['image'] ?? null,
                'has_embed'          => $entry['has_embed'] ?? 0,
                'user_id'            => $entry['user_id'] ?? null,
                'user_type'          => $entry['user_type'] ?? null,
                'owner_id'           => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'         => $entry['owner_type'] ?? $entry['user_type'],
                'location_latitude'  => $entry['location_latitude'] ?? null,
                'location_longitude' => $entry['location_longitude'] ?? null,
                'location_name'      => isset($entry['location_name']) ? html_entity_decode($entry['location_name']) : null,
            ]
        );
    }
}
