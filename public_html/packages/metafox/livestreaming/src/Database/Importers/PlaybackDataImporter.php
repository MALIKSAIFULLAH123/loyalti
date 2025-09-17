<?php

namespace MetaFox\LiveStreaming\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\LiveStreaming\Models\PlaybackData as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PlaybackDataImporter extends JsonImporter
{
    protected array $requiredColumns = ['live_id', 'playback_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$live']);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'          => $entry['$oid'],
            'live_id'     => $entry['live_id'],
            'playback_id' => $entry['playback_id'],
            'privacy'     => $this->privacyMapEntry($entry),
            'updated_at'  => $entry['updated_at'] ?? null,
            'created_at'  => $entry['created_at'] ?? null,
        ]);
    }
}
