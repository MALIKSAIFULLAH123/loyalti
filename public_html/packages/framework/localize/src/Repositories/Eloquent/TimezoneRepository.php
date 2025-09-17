<?php

namespace MetaFox\Localize\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MetaFox\Core\Support\Facades\Timezone as TimezoneFacade;
use MetaFox\Localize\Models\Timezone;
use MetaFox\Localize\Policies\TimezonePolicy;
use MetaFox\Localize\Repositories\TimezoneRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * @method Timezone find($id, $columns = ['*'])
 * @method Timezone getModel()
 */
class TimezoneRepository extends AbstractRepository implements TimezoneRepositoryInterface
{
    public function model(): string
    {
        return Timezone::class;
    }

    public function getName(?int $id)
    {
        if (!$id) {
            return null;
        }

        return Cache::rememberForever('timezone_' . $id, function () use ($id) {
            /** @var ?Timezone $model */
            $model = Timezone::query()->find($id);

            return $model?->name;
        });
    }

    public function getTimezoneByName(?string $name): ?Timezone
    {
        if (null === $name) {
            return null;
        }

        /** @var ?Timezone $model */
        $model = Timezone::query()->where('name', $name)->first();

        return $model;
    }

    public function getDefaultTimezoneId(): int
    {
        return (int) Cache::rememberForever('getDefaultTimezoneId', function () {
            $defaultTimezone = Settings::get('localize.default_timezone');

            if (is_string($defaultTimezone)) {
                /** @var \MetaFox\Localize\Models\Timezone $defaultTimezone */
                $defaultTimezone = TimezoneFacade::getTimezoneByName($defaultTimezone);

                return $defaultTimezone?->entityId();
            }

            return 0;
        });
    }

    public function getTimeZones(): array
    {
        return $this->getModel()->newQuery()
            ->where('is_active', Timezone::IS_ACTIVE)
            ->get()
            ->pluck([], 'id')
            ->toArray();
    }

    public function getActiveTimeZones(): Collection
    {
        return $this->getModel()->newQuery()
            ->where('is_active', Timezone::IS_ACTIVE)
            ->get();
    }

    public function getActiveTimeZonesForForm(): array
    {
        return $this->getModel()
            ->newQuery()
            ->get(['name', 'id', 'is_active'])
            ->where('is_active', 1)
            ->map(function (Timezone $timezone) {
                return ['label' => $timezone->name, 'value' => $timezone->id];
            })
            ->toArray();
    }

    /**
     * @return Timezone|null
     */
    public function getFirstActiveTimeZone(): ?Timezone
    {
        /** @var Timezone $timezone */
        $timezone = $this->getModel()->newQuery()
            ->where('is_active', Timezone::IS_ACTIVE)
            ->first();

        return $timezone;
    }

    /**
     * @throws AuthorizationException
     */
    public function updateActive(User $context, int $id, bool $isActive): bool
    {
        $timezone = $this->find($id);

        policy_authorize(TimezonePolicy::class, 'update', $context);

        $valueSetting = Settings::get('localize.default_timezone', '');

        if (strcmp($valueSetting, $timezone->name)) {
            Settings::save(['localize.default_timezone' => '']);
        }

        return $timezone->update(['is_active' => $isActive]);
    }
}
