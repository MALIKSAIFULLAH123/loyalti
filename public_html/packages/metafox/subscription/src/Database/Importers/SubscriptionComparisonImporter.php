<?php

namespace MetaFox\Subscription\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Importer\Models\Entry;
use MetaFox\Importer\Repositories\EntryRepositoryInterface;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Subscription\Models\SubscriptionComparison as Model;
use MetaFox\Subscription\Models\SubscriptionComparisonData;

/*
 * stub: packages/database/json-importer.stub
 */

class SubscriptionComparisonImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'] ?? null,
            'title'      => html_entity_decode($entry['title'] ?? ''),
            'updated_at' => $entry['updated_at'] ?? null,
            'created_at' => $entry['created_at'] ?? null,
        ]);

        $this->handleComparisonData($entry);
    }

    public function handleComparisonData(array $entry): void
    {
        $data = Arr::get($entry, '$data');

        foreach ($data as $key => $item) {
            $packageEntry = resolve(EntryRepositoryInterface::class)
                ->getEntry($key, $this->bundle->source);
            if (!$packageEntry) {
                continue;
            }
            $packageId    = $packageEntry->resource_id;
            $comparisonId = $entry['$oid'];

            $comData = SubscriptionComparisonData::query()
                ->where('package_id', $packageId)
                ->where('comparison_id', $comparisonId)
                ->first();

            if (!$comData instanceof SubscriptionComparisonData) {
                $comData = new SubscriptionComparisonData();
            }

            $comData->comparison_id = $comparisonId;
            $comData->package_id    = $packageId;
            $comData->type          = $item['type'];
            $comData->value         = $item['value'];

            $comData->save();
        }
    }
}
