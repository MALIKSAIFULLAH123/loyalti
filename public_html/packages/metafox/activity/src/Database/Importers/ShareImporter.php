<?php

namespace MetaFox\Activity\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Activity\Models\Share as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class ShareImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'owner_id', 'item_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$owner', '$user', '$item', '$parentFeed', '$parentModule',
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
                'is_approved'        => $entry['is_approved'] ?? 1,
                'total_like'         => $entry['total_like'] ?? 0,
                'total_comment'      => $entry['total_comment'] ?? 0,
                'total_reply'        => $entry['total_reply'] ?? 0,
                'total_share'        => $entry['total_share'] ?? 0,
                'total_view'         => $entry['total_view'] ?? 0,
                'user_id'            => $entry['user_id'] ?? null,
                'user_type'          => $entry['user_type'] ?? null,
                'owner_id'           => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'         => $entry['owner_type'] ?? $entry['user_type'],
                'item_id'            => $entry['item_id'] ?? null,
                'item_type'          => $entry['item_type'] ?? null,
                'location_latitude'  => $entry['location_latitude'] ?? null,
                'location_longitude' => $entry['location_longitude'] ?? null,
                'location_name'      => isset($entry['location_name']) ? html_entity_decode($entry['location_name']) : null,
                'content'            => $this->parseText($entry['content'] ?? '', false, true),
                'parent_feed_id'     => $entry['parentFeed_id'] ?? 0,
                'parent_module_id'   => $entry['parentModule_id'] ?? null,
                'created_at'         => $entry['created_at'] ?? null,
                'updated_at'         => $entry['updated_at'] ?? null,
            ]
        );
    }
}
