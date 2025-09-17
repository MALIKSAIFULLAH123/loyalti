<?php

namespace MetaFox\Localize\Database\Importers;

use MetaFox\Localize\Models\Currency as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class CurrencyImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'code',
        'symbol',
        'name',
        'format',
        'is_active',
        'is_default',
        'ordering',
    ];

    protected array $uniqueColumns = ['code'];

    protected array $requiredColumns = ['code'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['code']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'code'       => $entry['code'],
            'symbol'     => html_entity_decode($entry['symbol'] ?? ''),
            'name'       => $entry['name'] ?? '',
            'format'     => $entry['format'] ?? '',
            'is_active'  => $entry['is_active'] ?? 1,
            'is_default' => $entry['is_default'] ?? 0,
            'ordering'   => $entry['ordering'] ?? 0,
        ]);
    }
}
