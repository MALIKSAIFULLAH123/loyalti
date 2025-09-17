<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserPrivacy as Model;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;

/*
 * stub: packages/database/json-importer.stub
 */

class UserPrivacyImporter extends JsonImporter
{
    private array $privacyType;

    protected array $requiredColumns = ['privacy_id', 'user_id', 'name'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function beforeImport(): void
    {
        $this->privacyType = $this->getUserRepository()->getPrivacyTypes();
    }

    public function processImport()
    {
        $this->remapRefs([
            '$privacy',
            '$user',
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $privacyName = $this->getUserRepository()->convertPrivacySettingName($entry['name']);
        if (!array_key_exists($privacyName, $this->privacyType)) {
            return;
        }
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'user_id'    => $entry['user_id'],
            'type_id'    => $this->privacyType[$privacyName]['id'],
            'name'       => $privacyName,
            'privacy'    => $entry['privacy'],
            'privacy_id' => $entry['privacy_id'],
        ]);
    }

    public function getUserRepository(): UserPrivacyRepositoryInterface
    {
        return resolve(UserPrivacyRepositoryInterface::class);
    }
}
