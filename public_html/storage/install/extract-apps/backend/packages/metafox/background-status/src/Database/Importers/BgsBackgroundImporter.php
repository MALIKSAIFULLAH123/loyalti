<?php

namespace MetaFox\BackgroundStatus\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\BackgroundStatus\Models\BgsBackground as Model;
use MetaFox\Storage\Models\StorageFile;

/*
 * stub: packages/database/json-importer.stub
 */

class BgsBackgroundImporter extends JsonImporter
{
    protected array $requiredColumns = ['collection_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function beforePrepare(): void
    {
        Model::truncate();
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$image');
    }

    public function processImport()
    {
        $this->remapRefs([
            '$collection', '$image.$id' => ['image_file_id'],
        ]);

        $this->remapImage();

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'            => $entry['$oid'],
            'collection_id' => $entry['collection_id'] ?? null,
            'image_file_id' => $entry['image_file_id'] ?? null,
            'image_path'    => $entry['image_path'] ?? null,
            'server_id'     => $entry['server_id'] ?? null,
            'view_only'     => $entry['view_only'] ?? 0,
            'is_deleted'    => $entry['is_deleted'] ?? 0,
            'ordering'      => $entry['ordering'] ?? 0,
            'created_at'    => $entry['created_at'] ?? now(),
        ]);
    }

    private function remapImage(): void
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
}
