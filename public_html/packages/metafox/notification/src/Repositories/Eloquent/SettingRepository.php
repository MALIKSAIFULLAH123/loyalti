<?php

namespace MetaFox\Notification\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MetaFox\Notification\Models\ModuleSetting;
use MetaFox\Notification\Models\NotificationSetting;
use MetaFox\Notification\Repositories\NotificationChannelRepositoryInterface;
use MetaFox\Notification\Repositories\NotificationModuleRepositoryInterface;
use MetaFox\Notification\Repositories\SettingRepositoryInterface;
use MetaFox\Notification\Repositories\TypeRepositoryInterface;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Repositories\UserPreferenceRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\User as UserSupport;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class SettingRepository.
 * @method NotificationSetting getModel()
 */
class SettingRepository extends AbstractRepository implements SettingRepositoryInterface
{
    public function model()
    {
        return NotificationSetting::class;
    }

    public function userPreferencesRepository(): UserPreferenceRepositoryInterface
    {
        return resolve(UserPreferenceRepositoryInterface::class);
    }

    protected function moduleRepository(): NotificationModuleRepositoryInterface
    {
        return resolve(NotificationModuleRepositoryInterface::class);
    }

    protected function typeRepository(): TypeRepositoryInterface
    {
        return resolve(TypeRepositoryInterface::class);
    }

    public function notificationChannelRepository(): NotificationChannelRepositoryInterface
    {
        return resolve(NotificationChannelRepositoryInterface::class);
    }

    public function getChannelsForNotifiable(IsNotifiable $notifiable): array
    {
        $notifiableChannels = [];
        $query              = NotificationSetting::query()
            ->from('notification_types', 'types')
            ->select([
                'types.type',
                'types.can_edit',
                'modules.channel',
            ])
            ->join('notification_modules as modules', function (JoinClause $join) {
                match (DB::getDriverName() === 'mysql') {
                    true  => $join->on(DB::raw('CASE when `types`.`require_module_id` is not null then `types`.`require_module_id` ELSE `types`.`module_id` end'), '=', "modules.module_id"),
                    false => $join->on(DB::raw('CASE when "types"."require_module_id" is not null then "types"."require_module_id" ELSE "types"."module_id" end'), '=', "modules.module_id"),
                };
            })
            ->leftJoin('notification_module_settings as module_settings', function (JoinClause $join) use ($notifiable) {
                $join->on('module_settings.module_id', '=', 'modules.id');
                $join->where('module_settings.user_id', $notifiable->entityId());
            })
            ->leftJoin('notification_settings', function (JoinClause $join) use ($notifiable) {
                $join->on('notification_settings.type_id', '=', 'types.id');
                $join->on('notification_settings.channel', '=', 'modules.channel');
                $join->where('notification_settings.user_id', $notifiable->entityId());
            })
            ->where(function ($builder) {
                // either 1 or null is considered as activated
                $builder->where('module_settings.user_value', 1);
                $builder->orWhereNull('module_settings.user_value');
            })
            ->where('modules.is_active', 1)
            ->where(function ($builder) {
                // either 1 or null is considered as activated
                $builder->where('notification_settings.user_value', 1);
                $builder->orWhereNull('notification_settings.user_value');
            })
            ->whereIn('modules.channel', $this->notificationChannelRepository()->getActiveChannelNames());

        $user               = UserEntity::getById($notifiable->entityId())->detail;
        $channelsSubscribed = $this->getChannelsSubscribed($user);

        foreach ($query->cursor() as $item) {
            if (!in_array($item->channel, $channelsSubscribed) && $item->can_edit) {
                continue;
            }

            $notifiableChannels[$item->type][] = $item->channel;
        }

        return $notifiableChannels;
    }

    /**
     * @inheritDoc
     * @throws ValidationException
     */
    public function updateByChannel(User $context, array $attributes): bool
    {
        $module  = Arr::get($attributes, 'module_id');
        $type    = Arr::get($attributes, 'var_name');
        $setting = Arr::get($attributes, 'setting');
        if ($setting !== null) {
            $this->handleUpdateSubscribeChannel($context, $attributes);

            return true;
        }

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

    protected function handleUpdateSubscribeChannel(User $context, array $attributes): void
    {
        $setting = Arr::get($attributes, 'setting');
        $value   = Arr::get($attributes, 'value', 1);
        $channel = Arr::get($attributes, 'channel');

        $channelsSubscribed = $this->getChannelsSubscribed($context);

        if (in_array($channel, $channelsSubscribed) && !$value) {
            $channelsSubscribed = array_filter($channelsSubscribed, function ($item) use ($channel) {
                return $item !== $channel;
            });
        }

        if (!in_array($channel, $channelsSubscribed) && $value) {
            $channelsSubscribed[] = $channel;
        }

        $params = [
            $setting => array_values($channelsSubscribed),
        ];

        $this->userPreferencesRepository()->updateOrCreatePreferences($context, $params);
    }

    protected function handleUpdateTypeSetting(User $context, array $attributes): void
    {
        $channel  = Arr::get($attributes, 'channel');
        $typeName = Arr::get($attributes, 'var_name', '');
        $type     = $this->typeRepository()->findByField('type', $typeName)->first();
        $value    = Arr::get($attributes, 'value', 1);

        if (!$type->can_edit) {
            throw new AuthorizationException();
        }

        $this->getModel()->newQuery()
            ->updateOrCreate([
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

    /**
     * @param string[] $settings
     * @param string   $type
     *
     * @return void
     * @throws ValidationException
     */
    private function validateNotificationSettings(array $settings, string $type): void
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

    public function getChannelsSubscribed(User $context): array
    {
        $channelsSubscribed = $this->userPreferencesRepository()->getPreferences($context);
        $channelsDefault    = $this->notificationChannelRepository()->getAllChannelNames() ?? [];

        return Arr::get($channelsSubscribed, UserSupport::SUBSCRIBE_NOTIFICATION_CHANNELS, $channelsDefault) ?? [];
    }
}
