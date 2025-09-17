<?php

namespace MetaFox\ActivityPoint\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\ActivityPoint\Models\PointStatistic as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PointStatisticImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$user']);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'              => $entry['user_id'],
            'current_points'  => $entry['current_points'] ?? 0,
            'total_earned'    => $entry['total_earned'] ?? 0,
            'total_bought'    => $entry['total_bought'] ?? 0,
            'total_sent'      => $entry['total_sent'] ?? 0,
            'total_spent'     => $entry['total_spent'] ?? 0,
            'total_received'  => $entry['total_received'] ?? 0,
            'total_retrieved' => $entry['total_retrieved'] ?? 0,
            'updated_at'      => $entry['updated_at'] ?? null,
            'created_at'      => $entry['created_at'] ?? null,
        ]);
    }
}
