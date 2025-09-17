<?php

namespace MetaFox\Activity\Database\Importers;

use MetaFox\Core\Models\PrivacyStream;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Activity\Models\Post as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PostImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'owner_id'];

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
            '$owner', '$user', '$statusBackground',
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
                'id'                   => $oid,
                'privacy'              => $this->privacyMapEntry($entry),
                'is_approved'          => $entry['is_approved'] ?? 1,
                'total_like'           => $entry['total_like'] ?? 0,
                'total_comment'        => $entry['total_comment'] ?? 0,
                'total_reply'          => $entry['total_reply'] ?? 0,
                'total_share'          => $entry['total_share'] ?? 0,
                'user_id'              => $entry['user_id'] ?? null,
                'user_type'            => $entry['user_type'] ?? null,
                'owner_id'             => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'           => $entry['owner_type'] ?? $entry['user_type'],
                'location_latitude'    => $entry['location_latitude'] ?? null,
                'location_longitude'   => $entry['location_longitude'] ?? null,
                'location_name'        => isset($entry['location_name']) ? html_entity_decode($entry['location_name']) : null,
                'content'              => isset($entry['content']) ? $this->parseText($entry['content'], false, true, $entry) : null,
                'status_background_id' => $entry['statusBackground_id'] ?? 0,
                'created_at'           => $entry['created_at'] ?? null,
                'updated_at'           => $entry['updated_at'] ?? null,
            ]
        );
    }

    public function afterImport(): void
    {
        $this->processImportUserMention();
    }
}
