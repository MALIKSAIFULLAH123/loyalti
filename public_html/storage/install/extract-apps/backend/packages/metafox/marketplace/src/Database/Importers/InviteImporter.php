<?php

namespace MetaFox\Marketplace\Database\Importers;

use MetaFox\Marketplace\Models\Invite as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class InviteImporter extends JsonImporter
{
    protected array $requiredColumns = [
        'owner_id',
        'owner_type',
        'user_id',
        'user_type',
        'listing_id',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$user', '$owner', '$listing']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'           => $entry['$oid'],
            'owner_id'     => $entry['owner_id'] ?? null,
            'owner_type'   => $entry['owner_type'] ?? null,
            'user_id'      => $entry['user_id'] ?? null,
            'user_type'    => $entry['user_type'] ?? null,
            'listing_id'   => $entry['listing_id'] ?? null,
            'method_type'  => $entry['method_type'] ?? null,
            'method_value' => $entry['method_value'] ?? null,
            'visited_at'   => $entry['visited_at'] ?? null,
            'created_at'   => $entry['created_at'] ?? null,
            'updated_at'   => $entry['updated_at'] ?? null,
        ]);
    }
}
