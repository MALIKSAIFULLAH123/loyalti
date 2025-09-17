<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserValue as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserValueImporter extends JsonImporter
{
    protected array $requiredColumns = ['value', 'name', 'user_id'];

    // fill from data to model attributes.
    protected $fillable = [
        'name',
        'value',
        'default_value',
        'ordering',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    // batch to raw query in database.
    public function processImport()
    {
        $this->remapRefs([
            '$user' => ['user_id', 'user_type'],
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'            => $entry['$oid'],
            'name'          => $entry['name'],
            'value'         => $entry['value'],
            'default_value' => $entry['value'],
            'user_id'       => $entry['user_id'],
            'user_type'     => $entry['user_type'],
        ]);
    }
}
