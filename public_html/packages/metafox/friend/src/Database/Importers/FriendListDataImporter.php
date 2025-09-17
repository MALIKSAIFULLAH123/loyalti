<?php

namespace MetaFox\Friend\Database\Importers;

use MetaFox\Friend\Models\FriendListData;
use MetaFox\Friend\Models\FriendListData as Model;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class FriendListDataImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->transformPrivacyMember([], '$list', '$user');
    }

    public function processImport()
    {
        $this->remapRefs([
            '$list',
            '$user',
        ]);

        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(FriendListData::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $id = $entry['$oid'];

        $this->addEntryToBatch(FriendListData::class, [
            'id'        => $id,
            'list_id'   => $entry['list_id'],
            'user_id'   => $entry['user_id'],
            'user_type' => $entry['user_type'],
        ]);
    }

    public function getPrivacyList($entry): array
    {
        return [MetaFoxPrivacy::CUSTOM];
    }
}
