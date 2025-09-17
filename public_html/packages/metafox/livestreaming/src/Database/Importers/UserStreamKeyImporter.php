<?php

namespace MetaFox\LiveStreaming\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\LiveStreaming\Models\UserStreamKey as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserStreamKeyImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'stream_key', 'live_stream_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$asset',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(
            Model::class,
            [
                'id'             => $entry['$oid'],
                'user_id'        => $entry['user_id'] ?? null,
                'user_type'      => $entry['user_type'] ?? null,
                'asset_id'       => $entry['asset_id'] ?? null,
                'stream_key'     => $entry['stream_key'],
                'live_stream_id' => $entry['live_stream_id'],
                'playback_ids'   => isset($entry['playback_ids']) ? serialize($entry['playback_ids']) : null,
                'is_streaming'   => $entry['is_streaming'] ?? 0,
                'connected_from' => $entry['connected_from'] ?? 0,
                'updated_at'     => $entry['updated_at'] ?? null,
                'created_at'     => $entry['created_at'] ?? null,
            ]
        );
    }
}
