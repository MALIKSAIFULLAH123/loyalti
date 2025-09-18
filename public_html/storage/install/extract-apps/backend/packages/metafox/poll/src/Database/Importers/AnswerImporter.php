<?php

namespace MetaFox\Poll\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Poll\Models\Answer as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class AnswerImporter extends JsonImporter
{
    protected array $requiredColumns = ['poll_id'];

    private array $orderings = [];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$poll',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $pollId = $entry['poll_id'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'         => $entry['$oid'],
                'poll_id'    => $entry['poll_id'],
                'answer'     => html_entity_decode($entry['answer'] ?? ''),
                'percentage' => $entry['percentage'] ?? 0,
                'total_vote' => $entry['total_vote'] ?? 0,
                'ordering'   => $this->getOrdering($pollId),
                'updated_at' => $entry['updated_at'] ?? null,
                'created_at' => $entry['created_at'] ?? null,
            ]
        );
    }

    private function getKeyOrdering(int $pollId): string
    {
        return "poll#$pollId";
    }

    private function setOrdering(string $key, int $value): void
    {
        Arr::set($this->orderings, $key, $value);
    }

    private function getOrdering(int $pollId): int
    {
        $key = $this->getKeyOrdering($pollId);

        $currentOrdering = Arr::get($this->orderings, $key, 0);

        if (!$currentOrdering) {
            $currentOrdering = Model::query()
                ->where('poll_id', '=', $pollId)
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
