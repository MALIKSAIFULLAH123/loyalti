<?php

namespace MetaFox\Forum\Database\Importers;

use MetaFox\Forum\Models\Moderator as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class ModeratorImporter extends JsonImporter
{
    protected array $requiredColumns = ['forum_id', 'user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$forum',
            '$user',
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'        => $entry['$oid'],
            'forum_id'  => $entry['forum_id'],
            'user_id'   => $entry['user_id'],
            'user_type' => $entry['user_type'],
        ]);
    }
}
