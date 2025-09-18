<?php

namespace MetaFox\Quiz\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Quiz\Models\ResultDetail as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class ResultDetailImporter extends JsonImporter
{
    protected array $requiredColumns = ['result_id', 'question_id', 'answer_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$result', '$question', '$answer',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(
            Model::class,
            [
                'id'          => $entry['$oid'],
                'result_id'   => $entry['result_id'],
                'question_id' => $entry['question_id'],
                'answer_id'   => $entry['answer_id'],
                'is_correct'  => $entry['is_correct'] ?? 0,
            ]
        );
    }
}
