<?php

namespace MetaFox\Poll\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Poll\Models\Design as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class DesignImporter extends JsonImporter
{
    protected array $requiredColumns = ['$oid'];

    protected $fillable = [
        'percentage',
        'background',
        'border',
        'created_at',
        'updated_at',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$poll' => ['$oid'],
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'border'     => $entry['border'] ?? null,
            'background' => $entry['background'] ?? 'ebebeb',
            'percentage' => $entry['percentage'] ?? '297fc7',
            'updated_at' => $entry['updated_at'] ?? null,
            'created_at' => $entry['created_at'] ?? null,
        ]);
    }
}
