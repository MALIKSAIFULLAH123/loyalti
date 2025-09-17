<?php

namespace MetaFox\Storage\Database\Importers;

use Illuminate\Support\Facades\Cache;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Storage\Models\StorageFile as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class StorageFileImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    private function getTargetId(string $refId, string $source): string
    {
        return Cache::remember(
            'storage_file_importer_target_id' . $refId . $source,
            3600,
            function () use ($refId, $source) {
                $entry = $this->getEntryRepository()->getModel()->newQuery()->where('ref_id', $refId)->where('source', $source)->first();
                if (!empty($entry) && $entry->resource_id) {
                    $configName = SiteSetting::query()->where('id', $entry->resource_id)->first('config_name');
                    if ($configName) {
                        return str_replace('filesystems.disks.', '', $configName->config_name);
                    }
                }

                return 'public';
            }
        );
    }

    public function processImport()
    {
        $this->remapRefs([
            '$origin' => ['origin_id'],
            '$user'   => ['user_id', 'user_type'],
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $storageId = !empty($entry['is_photo']) ? 'photo' : 'attachment';
        $target    = app('storage')->getTarget($storageId);
        if (!empty($entry['$storage'])) {
            $target = $this->getTargetId($entry['$storage'], $this->bundle?->source);
        }
        $this->addEntryToBatch(Model::class, [
            'id'            => $entry['$oid'],
            'storage_id'    => $storageId,
            'target'        => $target,
            'origin_id'     => $entry['origin_id'],
            'is_origin'     => $entry['is_origin'] ?? 0,
            'variant'       => $entry['variant'] ?? 'origin',
            'original_name' => $entry['original_name'] ?? null,
            'file_size'     => $entry['file_size'] ?? null,
            'path'          => $entry['path'],
            'mime_type'     => $entry['mime_type'] ?? null,
            'extension'     => $entry['extension'] ?? null,
            'width'         => $entry['width'] ?? null,
            'height'        => $entry['height'] ?? null,
            'user_id'       => $entry['user_id'] ?? null,
            'user_type'     => $entry['user_type'] ?? null,
            'created_at'    => $entry['created_at'] ?? now(),
            'updated_at'    => $entry['updated_at'] ?? null,
        ]);
    }
}
