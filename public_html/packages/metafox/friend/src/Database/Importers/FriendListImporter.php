<?php

namespace MetaFox\Friend\Database\Importers;

use MetaFox\Core\Models\Privacy;
use MetaFox\Friend\Models\FriendList as Model;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class FriendListImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user' => ['user_id', 'user_type'],
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id'], 100);
    }

    protected function processImportEntry(array &$entry): void
    {
        $id = $entry['$oid'];

        if (!isset($entry['user_id'])) {
            return;
        }

        $this->addEntryToBatch(Model::class, [
            'id'         => $id,
            'name'       => $entry['name'],
            'user_id'    => $entry['user_id'],
            'user_type'  => $entry['user_type'],
            'created_at' => $entry['created_at'] ?? now(),
            'updated_at' => $entry['updated_at'] ?? null,
        ]);
        $this->addEntryToBatch(Privacy::class, [

        ]);
    }

    public function afterPrepare(): void
    {
        $this->transformPrivacyList(MetaFoxPrivacy::CUSTOM, 'user_friend_list', '$user', '$user');
        $this->transformPrivacyMember([MetaFoxPrivacy::CUSTOM], '$id', '$user');
    }
}
