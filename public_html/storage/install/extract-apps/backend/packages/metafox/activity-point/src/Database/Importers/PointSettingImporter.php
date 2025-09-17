<?php

namespace MetaFox\ActivityPoint\Database\Importers;

use MetaFox\ActivityPoint\Repositories\PointSettingRepositoryInterface;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\ActivityPoint\Models\PointSetting as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PointSettingImporter extends JsonImporter
{
    protected array $requiredColumns = ['role_id', 'name'];

    private array $aptSettings;

    private array $threadEntries = [];

    public function __construct()
    {
        $this->aptSettings = $this->mapOldData();
    }

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$role']);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['role_id', 'name']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oldData = $this->getOldData($entry);
        if (!$oldData) {
            return;
        }

        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'role_id'    => $entry['role_id'],
            'name'       => $entry['name'],
            'action'     => $oldData['action'] ?? '',
            'is_active'  => $entry['is_active'] ?? 1,
            'points'     => $entry['points'] ?? 0,
            'max_earned' => $entry['max_earned'] ?? 0,
            'period'     => $entry['period'] ?? 0,
        ]);
    }

    private function getKeySetting($setting): string
    {
        return $setting['role_id'] . '.' . $setting['name'];
    }

    private function getOldData(array $entry): ?array
    {
        $key = $this->getKeySetting($entry);

        if (!isset($this->aptSettings[$key])) {
            return null;
        }

        return $this->aptSettings[$key];
    }

    private function mapOldData(): array
    {
        $settings = resolve(PointSettingRepositoryInterface::class)
            ->getAllPointSetting()->toArray();

        $maps = [];

        foreach ($settings as $setting) {
            $key = $this->getKeySetting($setting);

            $maps[$key] = [
                'id'      => $setting['id'],
                'role_id' => $setting['role_id'],
                'action'  => $setting['action'],
            ];
        }

        return $maps;
    }

    public function beforeImport(): void
    {
        foreach ($this->entries as &$entry) {
            if ($entry['name'] != 'forum.create') {
                continue;
            }

            $entry = array_merge($entry, [
                'name'               => 'forum_post.create',
                'description_phrase' => 'activitypoint::phrase.forum_post_create_description',
            ]);

            $threadEntry = $entry;

            $threadEntry = array_merge($threadEntry, [
                '$id'                => $entry['$id'] . '.' . 'forum_thread',
                'name'               => 'forum_thread.create',
                'description_phrase' => 'activitypoint::phrase.forum_thread_create_description',
            ]);

            unset($threadEntry['$oid']);

            $this->threadEntries[] = $threadEntry;
        }
    }

    public function afterImport(): void
    {
        $this->exportBundledEntries($this->threadEntries, Model::ENTITY_TYPE, 40);
    }
}
