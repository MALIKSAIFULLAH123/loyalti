<?php

namespace MetaFox\Core\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Core\Models\Attachment as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class AttachmentImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    protected array $requiredColumns = ['user_id', 'user_type', 'file_id'];

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$file');
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$item',
            '$file.$id' => ['file_id'],
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'] ?? null,
            'file_id'    => $entry['file_id'],
            'user_id'    => $entry['user_id'],
            'user_type'  => $entry['user_type'],
            'item_type'  => $entry['item_type'] ?? null,
            'item_id'    => $entry['item_id'] ?? null,
            'updated_at' => $entry['updated_at'] ?? null,
            'created_at' => $entry['created_at'] ?? null,
        ]);
    }
}
