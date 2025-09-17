<?php

namespace MetaFox\Forum\Database\Importers;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Forum\Models\UserRolePermission as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserRolePermissionImporter extends JsonImporter
{
    protected array $requiredColumns = ['forum_id', 'role_id', 'permission_name'];

    public function getModelClass(): string
    {
        return Model::class;
    }
    public function processImport()
    {
        $this->remapRefs([
            '$forum',
            '$role',
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'               => $entry['$oid'],
            'forum_id'         => $entry['forum_id'],
            'role_id'          => $entry['role_id'],
            'permission_name'  => $entry['permission_name'] ?? '',
            'permission_value' => $entry['permission_value'] ?? false,
        ]);
    }
}
