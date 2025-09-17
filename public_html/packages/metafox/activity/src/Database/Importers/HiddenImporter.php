<?php

namespace MetaFox\Activity\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Activity\Models\Hidden as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class HiddenImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'feed_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$feed', '$user',
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
                'id'         => $oid,
                'user_id'    => $entry['user_id'] ?? null,
                'user_type'  => $entry['user_type'] ?? null,
                'feed_id'    => $entry['feed_id'] ?? null,
                'created_at' => $entry['created_at'] ?? null,
                'updated_at' => $entry['updated_at'] ?? null,
            ]
        );
    }
}
