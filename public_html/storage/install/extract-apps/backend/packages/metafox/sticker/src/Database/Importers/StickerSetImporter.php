<?php

namespace MetaFox\Sticker\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Sticker\Models\StickerSet as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class StickerSetImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'            => $oid,
                'title'         => html_entity_decode($entry['title'] ?? ''),
                'used'          => $entry['used'] ?? 0,
                'total_sticker' => $entry['total_sticker'] ?? 0,
                'is_default'    => $entry['is_default'] ?? 0,
                'is_active'     => $entry['is_active'] ?? 1,
                'thumbnail_id'  => $entry['thumbnail_id'] ?? 0,
                'image_path'    => $entry['image_path'] ?? null,
                'server_id'     => $entry['server_id'] ?? null,
                'ordering'      => $entry['ordering'] ?? 0,
                'view_only'     => $entry['view_only'] ?? 0,
                'is_deleted'    => $entry['is_deleted'] ?? 0,
            ]
        );
    }
}
