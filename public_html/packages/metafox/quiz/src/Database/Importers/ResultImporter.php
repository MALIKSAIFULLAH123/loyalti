<?php

namespace MetaFox\Quiz\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Quiz\Models\Result as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class ResultImporter extends JsonImporter
{
    protected array $requiredColumns = ['quiz_id', 'user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$quiz', '$user',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(
            Model::class,
            [
                'id'            => $entry['$oid'],
                'quiz_id'       => $entry['quiz_id'],
                'user_id'       => $entry['user_id'],
                'user_type'     => $entry['user_type'],
                'total_correct' => $entry['total_correct'] ?? 0,
                'updated_at'    => $entry['updated_at'] ?? null,
                'created_at'    => $entry['created_at'] ?? null,
            ]
        );
    }
}
