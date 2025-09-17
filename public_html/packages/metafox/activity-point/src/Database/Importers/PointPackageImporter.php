<?php

namespace MetaFox\ActivityPoint\Database\Importers;

use MetaFox\Importer\Models\Entry;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\ActivityPoint\Models\PointPackage as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PointPackageImporter extends JsonImporter
{
    protected array $requiredColumns = [];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$image');
    }

    public function processImport()
    {
        $this->remapRefs([
            '$image.$id' => ['image_file_id'],
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(Model::class, [
            'id'             => $oid,
            'title'          => html_entity_decode($entry['title'] ?? ''),
            'price'          => $this->handlePrice($entry['price'] ?? null),
            'image_file_id'  => $entry['image_file_id'] ?? null,
            'amount'         => $entry['amount'] ?? 0,
            'is_active'      => $entry['is_active'] ?? 1,
            'total_purchase' => $entry['total_purchase'] ?? 0,
            'updated_at'     => $entry['updated_at'] ?? null,
            'created_at'     => $entry['created_at'] ?? null,
        ]);
    }

    private function handlePrice(?array $prices): ?string
    {
        $result = [];

        foreach ($prices as $key => $price) {
            $currencyEntry = $this->getEntryRepository()
                ->getEntry($key, 'phpfox');

            if (!$currencyEntry instanceof Entry) {
                continue;
            }

            $currency          = explode('#', $currencyEntry->ref_id)[1];
            $result[$currency] = $price;
        }

        if (empty($result)) {
            return null;
        }

        return json_encode($result);
    }
}
