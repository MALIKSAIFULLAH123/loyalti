<?php

namespace MetaFox\ActivityPoint\Support;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\ActivityPoint\Contracts\Support\ActionType as ActionTypeContract;
use MetaFox\ActivityPoint\Models\ActionType as ActionTypeModel;
use MetaFox\ActivityPoint\Models\PointSetting;
use MetaFox\ActivityPoint\Models\PointTransaction;
use MetaFox\ActivityPoint\Support\ActivityPoint as SupportActivityPoint;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;

class ActionType implements ActionTypeContract
{
    public function getActionType(Entity $resource, string $action): ActionTypeModel|null
    {
        $resourceType = $resource->entityType();
        $nameSetting  = "$resourceType.$action";

        try {
            [, , , $packageId] = $this->driverRepository()->loadDriver(Constants::DRIVER_TYPE_ENTITY, $resourceType);
        } catch (\Exception $e) {
            return null;
        }

        return ActionTypeModel::query()
            ->where('name', '=', $nameSetting)
            ->where('package_id', '=', $packageId)
            ->first();
    }

    public function setupDefaultActionTypes(?string $packageId = null): void
    {
        $actions = ActionTypeModel::DEFAULT_ACTION_TYPES;

        $entities        = $this->loadEntities($packageId);
        $actionTypesData = $this->createDefaultActionTypesData($entities, $actions);

        $this->upsertActionTypes($actionTypesData);
    }

    public function setupCustomActionTypes(?string $packageId = null): void
    {
        $actions = $this->loadActions($packageId);

        $actionTypesData = $this->createCustomActionTypesData($actions);

        $this->upsertActionTypes($actionTypesData);
    }

    public function setupActionTypesInterpolateFromTransaction(?string $packageId = null): void
    {
        $actionTypesData = [];

        foreach ($this->transactionMapping() as $transactionPackageId => $actions) {
            if (!$this->shouldContinueForTransactionMapping($packageId, $transactionPackageId)) {
                continue;
            }

            foreach ($actions as $action) {
                $transactionPackageId = Arr::get($action, 'action_type_package_id', $transactionPackageId);
                $actionKey            = $transactionPackageId . $action['name'];

                if (Arr::has($actionTypesData, $actionKey)) {
                    continue;
                }

                $actionTypesData[$actionKey] = [
                    'package_id'   => $transactionPackageId,
                    'name'         => $action['name'],
                    'label_phrase' => $action['label_phrase'],
                ];
            }
        }

        $this->upsertActionTypes(array_values($actionTypesData));
    }

    public function createActionTypesData(string $packageId, mixed $resource, array $actions): array
    {
        if (!$this->shouldCreateActionTypesData($packageId, $resource, $actions)) {
            return [];
        }

        $actionTypesData = [];

        foreach ($actions as $action) {
            $name        = sprintf('%s.%s', $resource->entityType(), $action);
            $description = sprintf('activitypoint::phrase.%s_%s_description', $resource->entityType(), $action);

            $actionTypesData[] = [
                'package_id'   => $packageId,
                'name'         => $name,
                'label_phrase' => $description,
            ];
        }

        return $actionTypesData;
    }

    public function migrateTransactionExistPointSetting(?string $packageId = null): void
    {
        $query = PointTransaction::query()
            ->whereNull('action_id')
            ->whereNotNull('point_setting_id');

        $this->applyPackageFilter($query, $packageId);

        if ($query->count() === 0) {
            return;
        }

        $storePointSettings = $this->buildStorePointSettings($packageId);
        $storeConditions    = $this->buildStoreConditions($storePointSettings, $packageId);

        foreach ($storeConditions as $condition) {
            $query = PointTransaction::query()
                ->whereNull('action_id')
                ->whereIn('point_setting_id', $condition['setting_ids']);

            $this->applyPackageFilter($query, $packageId);

            $query->update(['action_id' => $condition['action_id']]);
        }
    }

    public function migrateTransactionNotExistPointSetting(?string $packageId = null): void
    {
        $query = PointTransaction::query()
            ->whereNull('action_id')
            ->whereNull('point_setting_id');

        $this->applyPackageFilter($query, $packageId);

        if ($query->count() === 0) {
            return;
        }

        $storeActions = $this->buildStoreActions();

        foreach ($this->transactionMapping() as $transactionPackageId => $actions) {
            if (!$this->shouldContinueForTransactionMapping($packageId, $transactionPackageId)) {
                continue;
            }

            foreach ($actions as $action) {
                $actionKey = Arr::get($action, 'action_type_package_id', $transactionPackageId) . $action['name'];
                $actionId  = Arr::get($storeActions, $actionKey);

                if (!$actionId) {
                    continue;
                }

                $conditions = $action['conditions'];
                $actions    = $conditions['actions'] ?? [];

                $query = PointTransaction::query()
                    ->where('package_id', $transactionPackageId)
                    ->where('type', $conditions['type']);

                if (!empty($actions)) {
                    $query->whereIn('action', Arr::wrap($actions));
                }

                $query->update(['action_id' => $actionId]);
            }
        }
    }

    public function getActionTypeOptions(): array
    {
        $packageScope = new PackageScope(resolve(ActionTypeModel::class)->getTable());
        $actions      = ActionTypeModel::query()
            ->addScope($packageScope)
            ->get(['id', 'label_phrase'])
            ->toArray();

        return array_map(function ($action) {
            return [
                'label' => __p($action['label_phrase']),
                'value' => $action['id'],
            ];
        }, $actions);
    }

    private function buildStorePointSettings(?string $packageId = null): array
    {
        $query = PointSetting::query();

        $this->applyPackageFilter($query, $packageId);

        $pointSettings      = $query->get(['id', 'name']);
        $storePointSettings = [];

        foreach ($pointSettings as $setting) {
            $oldIds                             = Arr::get($storePointSettings, $setting->name, []);
            $storePointSettings[$setting->name] = [...$oldIds, $setting->id];
        }

        return $storePointSettings;
    }

    private function buildStoreConditions(array $storePointSettings, ?string $packageId = null): array
    {
        $query = ActionTypeModel::query();

        $this->applyPackageFilter($query, $packageId);

        $actionTypes     = $query->get(['id', 'name']);
        $storeConditions = [];

        foreach ($actionTypes as $actionType) {
            $settingIds = Arr::get($storePointSettings, $actionType->name, []);

            if (empty($settingIds)) {
                continue;
            }

            $storeConditions[] = [
                'action_id'   => $actionType->id,
                'setting_ids' => $settingIds,
            ];
        }

        return $storeConditions;
    }

    private function buildStoreActions(): array
    {
        $actions      = ActionTypeModel::query()->get()->toArray();
        $storeActions = [];

        foreach ($actions as $action) {
            $storeActions[$action['package_id'] . $action['name']] = $action['id'];
        }

        return $storeActions;
    }

    private function loadEntities(?string $packageId): array
    {
        return $packageId ?
            $this->driverRepository()->loadDrivers(Constants::DRIVER_TYPE_ENTITY, null, true, null, null, $packageId) :
            $this->driverRepository()->loadDrivers(Constants::DRIVER_TYPE_ENTITY);
    }

    private function loadActions(?string $packageId): array
    {
        if (null === $packageId) {
            return PackageManager::discoverSettings('getActivityPointActions') ?? [];
        }

        return $this->getActivityPointActionsFromListener($packageId);
    }

    private function createDefaultActionTypesData(array $entities, array $actions): array
    {
        $actionTypesData = [];

        foreach ($entities as $entity) {
            [, $driver, , , $packageId] = $entity;
            $resource                   = resolve($driver);

            $actionTypesData = array_merge($actionTypesData, $this->createActionTypesData($packageId, $resource, $actions));
        }

        return $actionTypesData;
    }

    private function createCustomActionTypesData(array $customActions): array
    {
        $actionTypesData = [];

        foreach ($customActions as $config) {
            foreach ($config as $actions) {
                foreach ($actions as $action) {
                    $actionTypesData[] = [
                        'package_id'   => $action['package_id'],
                        'name'         => $action['name'],
                        'label_phrase' => $action['label_phrase'],
                    ];
                }
            }
        }

        return $actionTypesData;
    }

    private function getActivityPointActionsFromListener(string $packageId): array
    {
        $listener = PackageManager::getListener($packageId);

        if (!$listener) {
            return [];
        }

        if (!method_exists($listener, 'getActivityPointActions')) {
            return [];
        }

        $customActions = $listener->getActivityPointActions() ?? [];
        if (empty($customActions)) {
            return [];
        }

        $alias = PackageManager::getAlias($packageId);

        return [$alias => $customActions];
    }

    private function shouldCreateActionTypesData(string $packageId, mixed $resource, array $actions): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if ($this->getActivityPointService()->isCustomInstalled($packageId)) {
            return false;
        }

        if (empty($actions)) {
            return false;
        }

        return true;
    }

    private function shouldContinueForTransactionMapping(?string $packageId, string $transactionPackageId): bool
    {
        if ($packageId && $transactionPackageId !== $packageId) {
            return false;
        }

        return app_active($transactionPackageId);
    }

    private function applyPackageFilter(Builder $query, ?string $packageId): void
    {
        if ($packageId) {
            $query->where('package_id', $packageId);
        }
    }

    private function upsertActionTypes(array $data): void
    {
        ActionTypeModel::query()->upsert($data, ['package_id', 'name'], ['label_phrase']);
    }

    protected function getActivityPointService(): SupportActivityPoint
    {
        return resolve(SupportActivityPoint::class);
    }

    protected function driverRepository(): DriverRepositoryInterface
    {
        return resolve(DriverRepositoryInterface::class);
    }

    private function transactionMapping(): array
    {
        return [
            'metafox/advertise' => [
                [
                    'conditions' => [
                        'actions' => 'activitypoint::phrase.spent_activity_point_action',
                        'type'    => 4,
                    ],
                    'action_type_package_id' => 'metafox/activity-point',
                    'name'                   => ActionTypeModel::ACTIVITYPOINT_SPEND_POINTS_TYPE,
                    'label_phrase'           => 'activitypoint::phrase.action_type_spend_points_to_buy_item_label',
                ],
            ],
            'metafox/marketplace' => [
                [
                    'conditions' => [
                        'actions' => 'activitypoint::phrase.spent_activity_point_action',
                        'type'    => 4,
                    ],
                    'action_type_package_id' => 'metafox/activity-point',
                    'name'                   => ActionTypeModel::ACTIVITYPOINT_SPEND_POINTS_TYPE,
                    'label_phrase'           => 'activitypoint::phrase.action_type_spend_points_to_buy_item_label',
                ],
                [
                    'conditions' => [
                        'actions' => 'activitypoint::phrase.users_used_points_to_purchase_your_item',
                        'type'    => 5,
                    ],
                    'action_type_package_id' => 'metafox/activity-point',
                    'name'                   => ActionTypeModel::ACTIVITYPOINT_RECEIVE_POINTS_FROM_SELLING_ITEMS_TYPE,
                    'label_phrase'           => 'activitypoint::phrase.action_type_receive_points_from_selling_items_label',
                ],
            ],
            'metafox/subscription' => [
                [
                    'conditions' => [
                        'actions' => 'activitypoint::phrase.spent_activity_point_action',
                        'type'    => 4,
                    ],
                    'action_type_package_id' => 'metafox/activity-point',
                    'name'                   => ActionTypeModel::ACTIVITYPOINT_SPEND_POINTS_TYPE,
                    'label_phrase'           => 'activitypoint::phrase.action_type_spent_activity_point_label',
                ],
            ],
            'metafox/activity-point' => [
                [
                    'conditions' => [
                        'type' => 2,
                    ],
                    'name'         => ActionTypeModel::ACTIVITYPOINT_BUY_A_POINT_PACKAGE_TYPE,
                    'label_phrase' => 'activitypoint::phrase.action_type_buy_a_point_package_label',
                ],
                [
                    'conditions' => [
                        'actions' => [
                            'activitypoint::phrase.context_get_points_from',
                            'activitypoint::phrase.you_get_points_from',
                        ],
                        'type' => 5,
                    ],
                    'name'         => ActionTypeModel::ACTIVITYPOINT_RECEIVE_POINTS_TYPE,
                    'label_phrase' => 'activitypoint::phrase.action_type_receive_points_from_label',
                ],
                [
                    'conditions' => [
                        'actions' => [
                            'activitypoint::phrase.context_were_gifted_points_from_users',
                            'activitypoint::phrase.you_were_gifted_points_from_users',
                        ],
                        'type' => 5,
                    ],
                    'name'         => ActionTypeModel::ACTIVITYPOINT_GIFTED_POINTS_TYPE,
                    'label_phrase' => 'activitypoint::phrase.action_type_gifted_points_label',
                ],
                [
                    'conditions' => [
                        'actions' => [
                            'activitypoint::phrase.context_gifted_points_for_users',
                            'activitypoint::phrase.you_gifted_points_for_users',
                        ],
                        'type' => 3,
                    ],
                    'name'         => ActionTypeModel::ACTIVITYPOINT_GIFTING_POINTS_TYPE,
                    'label_phrase' => 'activitypoint::phrase.action_type_gifting_points_label',
                ],
                [
                    'conditions' => [
                        'actions' => 'activitypoint::phrase.convert_points_to_emoney',
                        'type'    => 4,
                    ],
                    'name'         => ActionTypeModel::ACTIVITYPOINT_CONVERT_POINTS_TO_EMONEY_TYPE,
                    'label_phrase' => 'activitypoint::phrase.action_type_convert_points_to_emoney_label',
                ],
                [
                    'conditions' => [
                        'actions' => 'activitypoint::phrase.your_point_has_been_revoked_by_the_administrator',
                        'type'    => 6,
                    ],
                    'name'         => ActionTypeModel::ACTIVITYPOINT_POINT_REVOCATION_TYPE,
                    'label_phrase' => 'activitypoint::phrase.action_type_point_revocation_label',
                ],
            ],
        ];
    }
}
