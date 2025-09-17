<?php

namespace MetaFox\Payment\Database\Importers;

use MetaFox\Payment\Models\Gateway as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class GatewayImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    protected array $uniqueColumns = ['service'];

    public function processImport()
    {
        $this->remapConfigPaypal();
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'            => $entry['$oid'] ?? null,
            'service'       => $entry['service'] ?? null,
            'is_active'     => $entry['is_active'] ?? false,
            'is_test'       => $entry['is_test'] ?? false,
            'config'        => $entry['config'] ?? '',
            'service_class' => $entry['service_class'] ?? '',
            'title'         => $entry['title'] ?? '',
            'description'   => $entry['description'] ?? '',
        ]);
    }

    private function remapConfigPaypal(): void
    {
        foreach ($this->entries as &$entry) {
            if ($entry['service'] != 'paypal') {
                continue;
            }

            $entry['service_class'] = \MetaFox\Paypal\Support\Paypal::class;
            $entry['config']        = json_encode(config('payment.gateways.paypal.config'));
        }
    }
}
