<?php

namespace MetaFox\ActivityPoint\Database\Importers;

use MetaFox\ActivityPoint\Repositories\PointSettingRepositoryInterface;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\ActivityPoint\Models\PointTransaction as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PointTransactionImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id'];

    private array $packageIds;

    public function __construct()
    {
        $this->packageIds = $this->getPackageIds();
    }

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$user', '$owner', '$pointSetting' => ['point_setting_id']]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        if (!$this->checkExistPackageId($entry['package_id'])) {
            return;
        }

        $this->addEntryToBatch(Model::class, [
            'id'               => $entry['$oid'],
            'user_id'          => $entry['user_id'] ?? null,
            'user_type'        => $entry['user_type'] ?? null,
            'owner_id'         => $entry['owner_id'] ?? $entry['user_id'],
            'owner_type'       => $entry['owner_type'] ?? $entry['user_type'],
            'module_id'        => $entry['module_id'] ?? null,
            'package_id'       => $entry['package_id'] ?? null,
            'point_setting_id' => $entry['point_setting_id'] ?? null,
            'type'             => $entry['type'] ?? 1,
            'action'           => $entry['action'],
            'points'           => $entry['points'] ?? 0,
            'is_hidden'        => $entry['is_hidden'] ?? 0,
            'action_params'    => $entry['action_params'] ?? null,
            'updated_at'       => $entry['updated_at'] ?? null,
            'created_at'       => $entry['created_at'] ?? null,
            'is_admincp'       => $entry['is_admincp'] ?? 0,
        ]);
    }

    private function checkExistPackageId(string $packageId): bool
    {
        return in_array($packageId, $this->packageIds);
    }

    private function getPackageIds(): array
    {
        $packageIds = resolve(PointSettingRepositoryInterface::class)
            ->getAllPackageId();
        $extraPackageIds = ['metafox/activity-point'];

        return array_merge($packageIds, $extraPackageIds);
    }
}
