<?php

namespace MetaFox\Like\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Like\Models\Reaction as Model;
use MetaFox\Storage\Models\StorageFile;

/*
 * stub: packages/database/json-importer.stub
 */

class ReactionImporter extends JsonImporter
{
    protected array $uniqueColumns = ['title'];

    private array $defaultReaction = [
        'like__u'  => 'like::phrase.like__u',
        'love__u'  => 'like::phrase.love__u',
        'haha__u'  => 'like::phrase.haha__u',
        'wow__u'   => 'like::phrase.wow__u',
        'sad__u'   => 'like::phrase.sad__u',
        'angry__u' => 'like::phrase.angry__u',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$icon');
    }

    public function processImport()
    {
        $this->remapRefs(['$icon.$id' => ['icon_file_id']]);
        $this->remapIcon();
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(Phrase::class, ['locale', 'key']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $isCustom = !isset($this->defaultReaction[$entry['phrase']]);

        if (!$isCustom) {
            return;
        }

        $this->addEntryToBatch(Model::class, [
            'id'           => $entry['$oid'],
            'title'        => $this->defaultReaction[$entry['phrase']] ?? 'like::phrase.' . $entry['phrase'],
            'is_active'    => $entry['is_active'] ?? 1,
            'is_default'   => $entry['is_default'] ?? 0,
            'color'        => $entry['color'] ?? '#2681D5',
            'icon_file_id' => $entry['icon_file_id'] ?? null,
            'image_path'   => $entry['image_path'] ?? null,
            'icon_path'    => $entry['image_path'] ?? null,
            'server_id'    => $entry['server_id'] ?? null,
            'ordering'     => $entry['ordering'] ?? null,
            'created_at'   => $entry['created_at'] ?? null,
            'updated_at'   => $entry['updated_at'] ?? null,
        ]);

        $this->addEntryToBatch(Phrase::class, [
            'key'        => 'like::phrase.' . $entry['phrase'],
            'name'       => $entry['phrase'],
            'group'      => 'phrase',
            'namespace'  => 'like',
            'package_id' => 'metafox/like',
            'locale'     => 'en',
            'text'       => $entry['title'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function remapIcon(): void
    {
        $values = $this->pickEntriesValue('icon_file_id');

        $map = [];

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

    public function beforePrepare(): void
    {
        foreach ($this->entries as &$entry) {
            if (isset($this->defaultReaction[$entry['phrase']])) {
                $entry['title'] = $this->defaultReaction[$entry['phrase']];
            }
        }
    }
}
