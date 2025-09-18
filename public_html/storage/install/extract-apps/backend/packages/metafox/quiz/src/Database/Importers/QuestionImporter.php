<?php

namespace MetaFox\Quiz\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Quiz\Models\Question as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class QuestionImporter extends JsonImporter
{
    protected array $requiredColumns = ['quiz_id'];

    private array $orderings = [];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$quiz',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $quizId = $entry['quiz_id'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'       => $entry['$oid'],
                'quiz_id'  => $quizId,
                'question' => $entry['question'] ?? '',
                'ordering' => $this->getOrdering($quizId),
            ]
        );
    }

    private function getKeyOrdering(int $quizId): string
    {
        return "quiz#$quizId";
    }

    private function setOrdering(string $key, int $value): void
    {
        Arr::set($this->orderings, $key, $value);
    }

    private function getOrdering(int $quizId): int
    {
        $key = $this->getKeyOrdering($quizId);

        $currentOrdering = Arr::get($this->orderings, $key, 0);

        if (!$currentOrdering) {
            $currentOrdering = Model::query()
                ->where('quiz_id', '=', $quizId)
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
