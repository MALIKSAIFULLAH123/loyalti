<?php

namespace MetaFox\Quiz\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Quiz\Models\Answer as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class AnswerImporter extends JsonImporter
{
    protected array $requiredColumns = ['question_id'];

    private array $orderings = [];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$question',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $questionId = $entry['question_id'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'          => $entry['$oid'],
                'question_id' => $questionId,
                'answer'      => $entry['answer'] ?? '',
                'ordering'    => $this->getOrdering($questionId),
                'is_correct'  => $entry['is_correct'] ?? 0,
            ]
        );
    }

    private function getKeyOrdering(int $questionId): string
    {
        return "question#$questionId";
    }

    private function setOrdering(string $key, int $value): void
    {
        Arr::set($this->orderings, $key, $value);
    }

    private function getOrdering(int $questionId): int
    {
        $key = $this->getKeyOrdering($questionId);

        $currentOrdering = Arr::get($this->orderings, $key, 0);

        if (!$currentOrdering) {
            $currentOrdering = Model::query()
                ->where('question_id', '=', $questionId)
                ->max('ordering');
        }

        if (!$currentOrdering) {
            $currentOrdering = 0;
        }

        $currentOrdering++;

        $this->setOrdering($key, $currentOrdering);

        return $currentOrdering;
    }
}
