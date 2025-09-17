<?php

namespace MetaFox\Sticker\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Sticker\Models\StickerUserValue as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class StickerUserValueImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$set', '$user',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'        => $oid,
                'user_id'   => $entry['user_id'] ?? null,
                'user_type' => $entry['user_type'] ?? null,
                'set_id'    => $entry['set_id'],
            ]
        );
    }
}
