<?php

namespace MetaFox\BackgroundStatus\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\BackgroundStatus\Models\BgsCollection as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class BgsCollectionImporter extends JsonImporter
{
    protected array $requiredColumns = ['main_background_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function beforePrepare(): void
    {
        Model::truncate();
    }

    public function processImport()
    {
        $this->remapRefs([
            '$mainBackground' => ['main_background_id'],
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'                 => $entry['$oid'],
            'title'              => $entry['title'] ?? null,
            'main_background_id' => $entry['main_background_id'] ?? 0,
            'is_active'          => $entry['is_active'] ?? 1,
            'is_default'         => $entry['is_default'] ?? 0,
            'view_only'          => $entry['view_only'] ?? 0,
            'is_deleted'         => $entry['is_deleted'] ?? 0,
            'total_background'   => $entry['total_background'] ?? 0,
            'created_at'         => $entry['created_at'] ?? now(),
        ]);
    }
}
