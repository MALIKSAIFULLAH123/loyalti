<?php

namespace MetaFox\ActivityPoint\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MetaFox\ActivityPoint\Models\PointSetting as Model;
use MetaFox\ActivityPoint\Policies\PointSettingPolicy;
use MetaFox\ActivityPoint\Repositories\PointSettingRepositoryInterface;
use MetaFox\ActivityPoint\Support\ActivityPoint;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Authorization\Repositories\Eloquent\RoleRepository;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;

/**
 * Class PointSettingRepository.
 *
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PointSettingRepository extends AbstractRepository implements PointSettingRepositoryInterface
{
    public function model(): string
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewSettings(User $context, array $attributes): Collection
    {
        policy_authorize(PointSettingPolicy::class, 'viewAny', $context);

        $sort     = $attributes['sort'];
        $sortType = $attributes['sort_type'];

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);
        $packageScope = new PackageScope($this->getModel()->getTable());
        $query        = $this->getModel()->newModelQuery();
        $roleId       = resolve(RoleRepository::class)->roleOf($context)->entityId();

        return $query
            ->addScope($sortScope)
            ->addScope($packageScope)
            ->whereHas('role', function (Builder $subQuery) use ($roleId) {
                $subQuery->where('id', '=', $roleId);
            })
            ->where('is_active', '=', 1)
            ->get()
            ->collect();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewSettingsAdmin(User $context, array $attributes): Paginator
    {
        policy_authorize(PointSettingPolicy::class, 'viewAny', $context);

        $sort     = $attributes['sort'];
        $sortType = $attributes['sort_type'];
        $limit    = $attributes['limit'];
        $search   = $attributes['module_id'];

        $packageScope = new PackageScope($this->getModel()->getTable());

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);
        $query = $this->getModel()->newModelQuery();

        if ($search != Browse::VIEW_ALL) {
            $query = $query->addScope(new SearchScope($search, ['module_id']));
        }

        $roleId = Arr::get($attributes, 'role_id', 0);
        if ($roleId > 0) {
            $query->whereHas('role', function (Builder $subQuery) use ($roleId) {
                $subQuery->where('id', '=', $roleId);
            });
        }

        return $query
            ->addScope($sortScope)
            ->addScope($packageScope)
            ->orderBy('id')
            ->paginate($limit);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function activateSetting(User $context, int $id): Model
    {
        /** @var Model $setting */
        $setting = $this->find($id);
        policy_authorize(PointSettingPolicy::class, 'update', $context, $setting);

        $setting->update(['is_active' => 1]);

        return $setting->refresh();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function deactivateSetting(User $context, int $id): Model
    {
        /** @var Model $setting */
        $setting = $this->find($id);
        policy_authorize(PointSettingPolicy::class, 'update', $context, $setting);

        $setting->update(['is_active' => 0]);

        return $setting->refresh();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function updateSetting(User $context, int $id, array $attributes): Model
    {
        /** @var Model $pointSetting */
        $pointSetting = $this->with(['role'])->find($id);
        policy_authorize(PointSettingPolicy::class, 'update', $context, $pointSetting);

        $pointSetting->fill($attributes);
        $pointSetting->save();

        return $pointSetting;
    }

    private function getPointSetting(User $user, string $nameSetting): ?Model
    {
        $role = resolve(RoleRepositoryInterface::class)->roleOf($user);

        return $this->getModel()
            ->newModelQuery()
            ->where('name', '=', $nameSetting)
            ->where('role_id', '=', $role->entityId())
            ->where('is_active', '=', 1)
            ->first();
    }

    private function checkPointIncreased(Model $setting, User $user): ?Model
    {
        $disabledFields = $setting->disabledFields;
        $maxEarned      = in_array('max_earned', $disabledFields) ? 99999999 : $setting->max_earned;
        $period         = in_array('period', $disabledFields) ? 0 : $setting->period; //in days

        if ($maxEarned == 0) {
            return $setting;
        }

        $query = $setting->transactions()
            ->where('user_id', '=', $user->entityId())
            ->where('owner_id', '=', $user->entityId())
            ->where('type', '=', ActivityPoint::TYPE_EARNED);

        if ($period > 0) {
            $query->where('created_at', '>', Carbon::now()->subDays($period));
        }

        $pointEarned = $query->get()->collect()->sum('points');

        $pointCanReceive = $maxEarned - $pointEarned;

        if ($pointCanReceive <= 0) {
            return null;
        }

        if ($pointCanReceive < $setting->points) {
            $setting->points = $pointCanReceive;
        }

        return $setting;
    }

    private function checkPointDecreased(Model $setting, User $user): ?Model
    {
        $currentPoint = $setting->transactions()
            ->where('user_id', '=', $user->entityId())
            ->where('owner_id', '=', $user->entityId())
            ->where('point_setting_id', '=', $setting->entityId())
            ->get()
            ->collect()
            ->sum('points');

        $afterDecreased = $currentPoint - $setting->points;

        if (is_int($afterDecreased) && $afterDecreased < 0) {
            return null;
        }

        return $setting;
    }

    /**
     * @inheritDoc
     */
    public function getUserPointSetting(User $user, Entity $resource, string $action, int $type): ?Model
    {
        $resourceType = $resource->entityType();
        $nameSetting  = "$resourceType.$action";

        $setting = $this->getPointSetting($user, $nameSetting);

        if (!$setting instanceof Model) {
            return null;
        }

        return match ($type) {
            ActivityPoint::TYPE_RETRIEVED => $this->checkPointDecreased($setting, $user),
            default                       => $this->checkPointIncreased($setting, $user),
        };
    }

    /**
     * @inheritDoc
     */
    public function getModuleOptions(): array
    {
        $key = 'PointSettingRepository::getModuleOptions';

        return Cache::rememberForever($key, function () {
            $result       = [];
            $packageScope = new PackageScope($this->getModel()->getTable());
            $query        = $this->getModel()->newModelQuery()
                ->addScope($packageScope)
                ->pluck(
                    'module_id',
                    'package_id'
                )->unique()->toArray();
            foreach ($query as $value) {
                $result[] = [
                    'label' => __p("$value::phrase.$value"),
                    'value' => $value,
                ];
            }

            return collect($result)->sortBy('label')->toArray();
        });
    }

    /**
     * @inheritDoc
     */
    public function getSettingActionsByPackageId(string $packageId): array
    {
        $result = [];
        $query  = $this->getModel()->newModelQuery()
            ->select('description_phrase')
            ->where('package_id', $packageId)
            ->groupBy('description_phrase')
            ->get()
            ->toArray();

        foreach ($query as $value) {
            $actionName = str_replace('description', 'action', $value['description_phrase']);

            $result[]   = [
                'label' => __p($actionName),
                'value' => $actionName,
            ];
        }

        return collect($result)->sortBy('label')->toArray();
    }

    public function getAllPointSetting(): Collection
    {
        return $this->getModel()->newModelQuery()->get();
    }

    public function getAllPackageId(): array
    {
        return $this->getModel()
            ->newModelQuery()
            ->select('package_id')
            ->groupBy('package_id')
            ->get()
            ->pluck('package_id')
            ->toArray();
    }

    public function clonePointSettings(int $destRoleId, int $sourceRoleId): void
    {
        $settings = $this->getModel()->newQuery()
            ->where([
                'role_id' => $sourceRoleId,
            ])
            ->get();

        if (!$settings->count()) {
            return;
        }

        $settings = array_map(function ($item) use ($destRoleId) {
            Arr::forget($item, ['id', 'created_at', 'updated_at']);
            Arr::set($item, 'role_id', $destRoleId);

            return $item;
        }, $settings->toArray());

        foreach ($settings as $setting) {
            $this->getModel()->newModelInstance($setting)->save();
        }
    }
}
