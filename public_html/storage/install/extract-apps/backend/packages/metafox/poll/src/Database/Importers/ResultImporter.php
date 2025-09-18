<?php

namespace MetaFox\Poll\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Poll\Models\Result as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class ResultImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'poll_id', 'answer_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$poll', '$user', '$answer',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(
            Model::class,
            [
                'id'         => $entry['$oid'],
                'poll_id'    => $entry['poll_id'],
                'answer_id'  => $entry['answer_id'],
                'user_id'    => $entry['user_id'],
                'user_type'  => $entry['user_type'],
                'updated_at' => $entry['updated_at'] ?? null,
                'created_at' => $entry['created_at'] ?? null,
            ]
        );
    }
}
