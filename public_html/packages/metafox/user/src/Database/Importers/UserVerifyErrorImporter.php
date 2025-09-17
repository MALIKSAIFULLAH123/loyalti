<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserVerifyError as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserVerifyErrorImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'hash_code',
        'ip_address',
        'email',
        'updated_at',
        'created_at',
    ];

    // fill from data to model refs.
    protected $relations = [];


    protected array $requiredColumns = [
        'hash_code',
        'email',
    ];

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
            'id'         => $entry['$oid'],
            'hash_code'  => $entry['hash_code'],
            'ip_address' => $entry['ip_address'] ?? null,
            'email'      => $entry['email'],
            'updated_at' => $entry['updated_at'] ?? null,
            'created_at' => $entry['created_at'] ?? now(),
        ]);
    }
}
