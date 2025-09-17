<?php

namespace MetaFox\Forum\Database\Importers;

use MetaFox\Forum\Models\Forum as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class ForumImporter extends JsonImporter
{

    protected bool $keepOldId = true;

    protected array $requiredColumns = ['title'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$parent' => ['parent_id', 'parent_type']]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $id = $entry['$oid'];
        $this->addEntryToBatch(Model::class, [
            'id'            => $id,
            'parent_id'     => $entry['parent_id'] ?? 0,
            'title'         => $entry['title'],
            'ordering'      => $entry['ordering'] ?? null,
            'is_closed'     => $entry['is_closed'] ?? null,
            'total_thread'  => $entry['total_thread'] ?? 0,
            'total_comment' => $entry['total_comment'] ?? 0,
            'total_sub'     => $entry['total_sub'] ?? 0,
            'level'         => 1, // Update later
            'description'   => $entry['description'] ?? null,
            'created_at'    => $entry['created_at'] ?? now(),
            'updated_at'    => $entry['updated_at'] ?? now(),
        ]);
    }
}
