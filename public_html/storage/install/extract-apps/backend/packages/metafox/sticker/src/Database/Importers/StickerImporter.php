<?php

namespace MetaFox\Sticker\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Sticker\Models\Sticker as Model;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Storage\Models\StorageFile;

/*
 * stub: packages/database/json-importer.stub
 */

class StickerImporter extends JsonImporter
{
    protected array $requiredColumns = ['set_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$image');
    }

    public function processImport()
    {
        $this->remapRefs(['$set', '$image.$id' => ['image_file_id']]);

        $this->remapSticker();

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
                'set_id'        => $entry['set_id'],
                'image_file_id' => $entry['image_file_id'] ?? null,
                'image_path'    => $entry['image_path'] ?? null,
                'server_id'     => $entry['server_id'] ?? null,
                'ordering'      => $entry['ordering'] ?? 0,
                'view_only'     => $entry['view_only'] ?? 0,
                'is_deleted'    => $entry['is_deleted'] ?? 0,
            ]
        );
    }

    private function remapSticker(): void
    {
        $values = $this->pickEntriesValue('image_file_id');

        $map  = [];
        $rows = StorageFile::query()->whereIn('id', $values)
            ->get(['id', 'path', 'storage_id'])
            ->toArray();

        array_map(function ($row) use (&$map) {
            $map[$row['id']] = [$row['path'], $row['storage_id']];
        }, $rows);

        foreach ($this->entries as &$entry) {
            $key = Arr::get($entry, 'image_file_id');

            if (!$key) {
                continue;
            }

            $item = $map[$key] ?? null;

            if (!$item) {
                continue;
            }

            $entry['image_path'] = $item[0];
            $entry['server_id']  = $item[1];
        }
    }

    public function afterImport(): void
    {
        foreach ($this->entries as $entry) {
            if (!Arr::get($entry, 'is_thumbnail')) {
                continue;
            }

            StickerSet::query()
                ->where('id', '=', $entry['set_id'])
                ->update(['thumbnail_id' => $entry['$oid']]);
        }
    }
}
