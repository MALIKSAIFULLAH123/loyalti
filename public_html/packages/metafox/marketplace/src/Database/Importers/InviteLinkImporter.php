<?php

namespace MetaFox\Marketplace\Database\Importers;

use MetaFox\Marketplace\Models\InviteLink as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class InviteLinkImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'code',
        'status',
        'expired_at',
        'created_at',
        'updated_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'listing',
    ];

    protected array $uniqueColumns = ['code'];

    protected array $requiredColumns = ['code'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$listing']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'code'       => $entry['code'],
            'listing_id' => $entry['listing_id'],
            'status'     => $entry['status'] ?? null,
            'expired_at' => $entry['expired_at'] ?? null,
            'created_at' => $entry['created_at'] ?? now(),
            'updated_at' => $entry['updated_at'] ?? null,
        ]);
    }
}
