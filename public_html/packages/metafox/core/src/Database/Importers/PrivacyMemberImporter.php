<?php

namespace MetaFox\Core\Database\Importers;

use MetaFox\Core\Models\PrivacyMember as Model;
use MetaFox\Platform\Support\JsonImporter;

class PrivacyMemberImporter extends JsonImporter
{
    protected array $modelClass = [];

    protected array $requiredColumns = ['user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    // batch to raw query in database.
    public function processImport()
    {
        $this->remapRefs([
            '$user' => ['user_id', 'user_type'],
            '$privacy',
        ]);

        $this->processImportEntries();
        foreach ($this->modelClass as $model) {
            $this->upsertBatchEntriesInChunked($model, ['privacy_id', 'user_id']);
        }
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];
        if (!in_array($entry['privacy_class'], $this->modelClass)) {
            $this->modelClass[] = $entry['privacy_class'];
        }
        $privacyId = $entry['privacy_id'] ?? $entry['default_privacy_id'];
        if ($privacyId === null) {
            return;
        }
        $this->addEntryToBatch($entry['privacy_class'], [
            'id'         => $oid,
            'user_id'    => $entry['user_id'],
            'privacy_id' => $privacyId,
        ]);
    }
}
