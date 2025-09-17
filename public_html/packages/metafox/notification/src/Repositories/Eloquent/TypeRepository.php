<?php

namespace MetaFox\Notification\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MetaFox\Notification\Contracts\TypeManager;
use MetaFox\Notification\Models\ModuleSetting;
use MetaFox\Notification\Models\NotificationChannel;
use MetaFox\Notification\Models\NotificationModule;
use MetaFox\Notification\Models\NotificationSetting;
use MetaFox\Notification\Models\Type;
use MetaFox\Notification\Models\TypeChannel;
use MetaFox\Notification\Policies\TypePolicy;
use MetaFox\Notification\Repositories\NotificationChannelRepositoryInterface;
use MetaFox\Notification\Repositories\NotificationModuleRepositoryInterface;
use MetaFox\Notification\Repositories\TypeChannelRepositoryInterface;
use MetaFox\Notification\Repositories\TypeRepositoryInterface;
use MetaFox\Notification\Support\Browse\Scopes\ModuleScope;
use MetaFox\Notification\Support\ChannelManager;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class TypeRepository.
 *
 * @ignore
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TypeRepository extends AbstractRepository implements TypeRepositoryInterface
{
    public function model(): string
    {
        return Type::class;
    }

    public function viewTypes(array $attributes): Collection
    {
        $query    = $this->getModel()->query();
        $search   = Arr::get($attributes, 'q');
        $moduleId = Arr::get($attributes, 'module_id');
        $table    = $this->getModel()->getTable();

        if ($search) {
            $searchScope = new SearchScope();
            $searchScope->setTableField('title');
            $searchScope->setJoinedTable('phrases');
            $searchScope->setAliasJoinedTable('ps');
            $searchScope->setJoinedField('key');
            $searchScope->setFields(['type', 'ps.text'])->setSearchText($search);
            $query = $query->addScope($searchScope);
        }

        if ($moduleId) {
            $moduleScope = new ModuleScope();
            $moduleScope->setModuleId($moduleId);
            $query = $query->addScope($moduleScope);
        }

        $query->addScope(resolve(PackageScope::class, [
            'table' => $table,
        ]));

        $query->join('packages', 'packages.alias', '=', DB::raw("CASE when $table.require_module_id is not null then $table.require_module_id ELSE $table.module_id end"))
            ->addSelect(["$table.*", 'packages.title as packages_title'])
            ->distinct();

        return $query->orderBy('packages_title')->get();
    }

    protected function typeChannelRepository(): TypeChannelRepositoryInterface
    {
        return resolve(TypeChannelRepositoryInterface::class);
    }

    protected function channelRepository(): NotificationChannelRepositoryInterface
    {
        return resolve(NotificationChannelRepositoryInterface::class);
    }

    protected function moduleRepository(): NotificationModuleRepositoryInterface
    {
        return resolve(NotificationModuleRepositoryInterface::class);
    }

    public function updateType(User $context, int $id, array $attributes): Type
    {
        /** @var Type $resource */
        $resource = $this->find($id);

        policy_authorize(TypePolicy::class, 'update', $context, $resource);

        $resource->fill($attributes);

        $resource->save();

        $typeManager = resolve(TypeManager::class);
        $typeManager->refresh();

        return $resource;
    }

    public function deleteType(User $context, int $id): int
    {
        $resource = $this->find($id);
        policy_authorize(TypePolicy::class, 'delete', $context, $resource);

        $response = $this->delete($id);

        $typeManager = resolve(TypeManager::class);
        $typeManager->refresh();

        return $response;
    }

    /**
     * @param User   $context
     * @param string $channel
     *
     * @return array<int, mixed>
     */
    public function getNotificationSettingsByChannel(User $context, string $channel): array
    {
        $modelChannel = $this->channelRepository()->getModel()->newQuery()
            ->where('name', $channel)
            ->first();

        if ($modelChannel instanceof NotificationChannel && $modelChannel->isDisable()) {
            return [];
        }

        $dataModuleTypes = [];
        $moduleChannels  = $this->moduleRepository()->getModulesByChannel($channel);
        $typeChannels    = $this->typeChannelRepository()->getTypesByChannel($channel);

        $userTypeValues = NotificationSetting::query()
            ->where('user_id', $context->entityId())
            ->where('channel', $channel)
            ->get()
            ->pluck([], 'type_id')
            ->toArray();

        $userModuleValues = ModuleSetting::query()
            ->where('user_id', $context->entityId())
            ->get()->pluck([], 'module_id')->toArray();

        foreach ($moduleChannels as $module) {
            $value = isset($userModuleValues[$module->entityId()])
                ? $userModuleValues[$module->entityId()]['user_value']
                : $module->is_active;

            /* @var NotificationModule $module */
            $dataModuleTypes[$module->module_id] = [
                'app_name'  => __p($module->module_id . '::phrase.' . $module->module_id),
                'module_id' => $module->module_id,
                'phrase'    => __p($module->title),
                'value'     => $value,
                'channel'   => $module->channel,
                'type'      => [],
            ];
        }

        foreach ($typeChannels as $typeChannel) {
            if (!$typeChannel instanceof TypeChannel) {
                continue;
            }

            if (!$this->canUpdateType($typeChannel, $dataModuleTypes)) {
                continue;
            }

            $type   = $typeChannel->type;
            $module = $type->require_module_id ?? $type->module_id;
            $value  = isset($userTypeValues[$typeChannel->type_id])
                ? $userTypeValues[$typeChannel->type_id]['user_value']
                : $type->is_active;

            $dataModuleTypes[$module]['type'][] = [
                'var_name' => $type->type,
                'phrase'   => __p($type->title),
                'value'    => (int) $value,
                'channel'  => $typeChannel->channel,
            ];
        }

        $values = array_values(array_filter($dataModuleTypes, fn ($x) => !empty($x['type'])));

        app('events')->dispatch('notification.settings_by_channel.override', [$context, $channel, &$values]);

        return array_values($values);
    }

    protected function canUpdateType(TypeChannel $typeChannel, array $dataModuleTypes): bool
    {
        $type = $typeChannel->type;
        if (empty($type)) {
            return false;
        }

        $module = $type->require_module_id ?? $type->module_id;
        if (!array_key_exists($module, $dataModuleTypes) || !$type->can_edit) {
            return false;
        }

        if (empty($type->handler)) {
            return false;
        }

        $hasSupportViaChannel = $this->channelManager()->hasSupportSendNotifyViaChannel(resolve($type->handler), $typeChannel->channel);
        if (!$hasSupportViaChannel) {
            return false;
        }

        return true;
    }

    protected function channelManager(): ChannelManager
    {
        return resolve(ChannelManager::class);
    }

    /**
     * @inheritDoc
     * @throws ValidationException
     * @deprecated moved to class SettingRepository
     */
    public function updateNotificationSettingsByChannel(User $context, array $attributes): bool
    {
        $module = Arr::get($attributes, 'module_id');
        $type   = Arr::get($attributes, 'var_name');
        if ($module !== null) {
            $this->handleUpdateModuleSetting($context, $attributes);

            return true;
        }

        if ($type !== null) {
            $this->handleUpdateTypeSetting($context, $attributes);

            return true;
        }

        return false;
    }

    /**
     * @throws ValidationException
     */
    protected function handleUpdateTypeSetting(User $context, array $attributes): void
    {
        $channel  = Arr::get($attributes, 'channel');
        $typeName = Arr::get($attributes, 'var_name', '');
        $type     = $this->findByField('type', $typeName)->first();
        $value    = Arr::get($attributes, 'value', 1);

        NotificationSetting::query()->updateOrCreate([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'type_id'   => $type->id,
            'channel'   => $channel,
        ], [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'type_id'    => $type->id,
            'user_value' => $value,
        ]);
    }

    /**
     * @throws ValidationException
     */
    protected function handleUpdateModuleSetting(User $context, array $attributes): void
    {
        $channel        = Arr::get($attributes, 'channel');
        $module         = Arr::get($attributes, 'module_id');
        $value          = Arr::get($attributes, 'value', 1);
        $moduleChannels = $this->moduleRepository()->getModulesByChannel($channel);
        $modules        = collect($moduleChannels)->pluck([], 'module_id')->toArray();

        $this->validateNotificationSettings($modules, $module);

        ModuleSetting::query()->updateOrCreate([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'module_id' => $modules[$module]['id'],
        ], [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'module_id'  => $modules[$module]['id'],
            'user_value' => $value,
        ]);
    }

    protected function handleGetTypeSettings(User $context, string $channel, array $dataModuleType): array
    {
        $typeChannels = $this->typeChannelRepository()
            ->getTypesByChannel($channel);

        $userTypeValues = NotificationSetting::query()
            ->where('user_id', $context->entityId())
            ->where('channel', $channel)
            ->get()
            ->pluck([], 'type_id')
            ->toArray();

        $dataModuleType['type'] = [];
        foreach ($typeChannels as $typeChannel) {
            /** @var Type $type */
            $type = $typeChannel->type;
            if (empty($type)) {
                continue;
            }

            if ($dataModuleType['module_id'] == $type->module_id) {
                $value = isset($userTypeValues[$typeChannel->type_id])
                    ? $userTypeValues[$typeChannel->type_id]['user_value']
                    : $type->is_active;

                $dataModuleType['type'][] = [
                    'var_name' => $type->type,
                    'phrase'   => __p($type->title),
                    'value'    => (int) $value,
                    'channel'  => $typeChannel->channel,
                ];
            }
        }

        return $dataModuleType;
    }

    /**
     * @param string[] $settings
     * @param string   $type
     *
     * @return void
     * @throws ValidationException
     */
    private function validateNotificationSettings(array $settings, string $type)
    {
        if (!isset($settings[$type])) {
            throw ValidationException::withMessages([
                __p(
                    'notification::phrase.notification_setting_not_exist',
                    ['attribute' => $type]
                ),
            ]);
        }
    }

    public function getAllNotificationType(): array
    {
        return $this->getModel()
            ->newModelQuery()
            ->get()
            ->pluck('type')
            ->toArray();
    }

    public function getNotificationTypeByType(string $type): ?Type
    {
        return $this->getModel()->newModelQuery()->where('type', $type)->with(['typeChannels'])->first();
    }
}
