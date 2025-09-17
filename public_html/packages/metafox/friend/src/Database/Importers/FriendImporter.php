<?php

namespace MetaFox\Friend\Database\Importers;

use MetaFox\Friend\Models\Friend as Model;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class FriendImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'created_at',
        'updated_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'user',
        'owner',
    ];

    protected array $requiredColumns = [
        'owner_id', 'user_id',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$user', '$owner']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->transformPrivacyMember([MetaFoxPrivacy::FRIENDS], '$user', '$owner');
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'user_id'    => $entry['user_id'],
            'user_type'  => $entry['user_type'],
            'owner_id'   => $entry['owner_id'],
            'owner_type' => $entry['owner_type'],
            'created_at' => $entry['created_at'] ?? now(),
            'updated_at' => $entry['updated_at'] ?? now(),
        ]);
    }

    public function afterPrepare(): void
    {
        $this->transformActivitySubscription();
    }
}
