<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserVerify as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserVerifyImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'email',
        'hash_code',
        'action',
        'expires_at',
        'created_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'user',
    ];

    protected array $requiredColumns = [
        'user_id',
        'email',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$user']);
        $this->addEntryToBatch(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'user_id'    => $entry['user_id'],
            'user_type'  => $entry['user_type'],
            'verifiable' => $entry['email'] ?? null,
            'hash_code'  => $entry['hash_code'] ?? null,
            'action'     => $entry['action'] ?? Model::ACTION_EMAIL,
            'expires_at' => $entry['expires_at'] ?? null,
            'created_at' => $entry['created_at'] ?? null,
        ]);
    }
}
