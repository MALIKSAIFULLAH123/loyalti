<?php

namespace MetaFox\Forum\Database\Importers;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Forum\Models\ModeratorAccess as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class ModeratorAccessImporter extends JsonImporter
{
    protected array $requiredColumns = ['moderator_id', 'permission_name'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$moderator',
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'              => $entry['$oid'],
            'moderator_id'    => $entry['moderator_id'],
            'permission_name' => $entry['permission_name'],
        ]);
    }
}
