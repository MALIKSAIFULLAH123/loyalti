<?php

namespace MetaFox\Advertise\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Jobs\DeletePlacementJob;
use MetaFox\Advertise\Models\Placement;
use MetaFox\Advertise\Policies\PlacementPolicy;
use MetaFox\Advertise\Repositories\PlacementRepositoryInterface;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class PlacementRepository.
 */
class PlacementRepository extends AbstractRepository implements PlacementRepositoryInterface
{
    public const PLACEMENTS_FOR_ADVERTISE_CACHE_ID = 'advertise_active_placements';

    public function model()
    {
        return Placement::class;
    }

    public function viewPlacementsInAdminCP(User $context, array $attributes = []): Collection
    {
        policy_authorize(PlacementPolicy::class, 'viewAdminCP', $context);

        return Placement::query()
            ->withCount(['advertises'])
            ->orderBy('ordering')
            ->orderBy('id')
            ->get();
    }

    public function createPlacement(User $context, array $attributes): ?Placement
    {
        policy_authorize(PlacementPolicy::class, 'create', $context);

        $placement = $this->getModel()->newModelInstance($attributes);

        $placement->save();

        $placement->refresh();

        return $placement;
    }

    public function updatePlacement(User $context, int $id, array $attributes): ?Placement
    {
        $placement = $this->find($id);

        policy_authorize(PlacementPolicy::class, 'update', $context, $placement);

        $placement->fill($attributes);

        $placement->save();

        $placement->refresh();

        return $placement;
    }

    public function deletePlacement(User $context, int $id, string $deleteOption, ?int $alternativeId = null): bool
    {
        $placement = $this->find($id);

        policy_authorize(PlacementPolicy::class, 'delete', $context, $placement);

        $placement->delete();

        DeletePlacementJob::dispatch($id, $deleteOption, $alternativeId);

        return true;
    }

    public function getMigrationOptions(int $deletedId): array
    {
        $placement = $this->find($deletedId);

        return Placement::query()
            ->where('id', '<>', $deletedId)
            ->where('placement_type', '=', $placement->placement_type)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get()
            ->map(function ($placement) {
                return [
                    'label' => $placement->toTitle(),
                    'value' => $placement->entityId(),
                ];
            })
            ->toArray();
    }

    protected function getPlacements(?bool $isActive = true): Collection
    {
        $placements = localCacheStore()->rememberForever(self::PLACEMENTS_FOR_ADVERTISE_CACHE_ID, function () {
            return Placement::query()
                ->orderBy('ordering')
                ->orderBy('id')
                ->get();
        });

        if (is_bool($isActive)) {
            $placements = $placements->filter(function ($placement) use ($isActive) {
                return $placement->is_active == $isActive;
            });
        }

        return $placements;
    }

    public function getActivePlacementsForSetting(): array
    {
        return $this->getPlacements()
            ->map(function ($placement) {
                return [
                    'label' => $placement->toTitle(),
                    'value' => $placement->entityId(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function getPlacementsForAdvertise(User $context, bool $isFree = false, ?bool $isActive = true): Collection
    {
        $placements = $this->getPlacements($isActive);

        if ($isFree) {
            return $placements;
        }

        $userCurrency = app('currency')->getUserCurrencyId($context);

        return $placements->filter(function ($placement) use ($userCurrency) {
            $price = $placement->price;

            if (!is_array($price)) {
                return false;
            }

            if (!count($price)) {
                return false;
            }

            if (!Arr::has($price, $userCurrency)) {
                return false;
            }

            if (null === Arr::get($price, $userCurrency)) {
                return false;
            }

            return true;
        });
    }

    public function activePlacement(User $context, int $id, bool $isActive): bool
    {
        $placement = $this->find($id);

        policy_authorize(PlacementPolicy::class, 'update', $context, $placement);

        $placement->update(['is_active' => $isActive]);

        $this->clearCaches();

        return true;
    }

    public function getPlacementPriceByCurrencyId(int $placementId, string $currencyId): ?float
    {
        $placement = Placement::query()
            ->where('id', '=', $placementId)
            ->first();

        if (null === $placement) {
            return null;
        }

        if (!is_array($placement->price)) {
            return null;
        }

        $price = Arr::get($placement->price, $currencyId);

        if (null === $price) {
            return null;
        }

        return (float) $price;
    }

    public function getAvailablePlacements(User $user, ?bool $isActive = true): array
    {
        $activePlacements = $this->getPlacementsForAdvertise($user, true, $isActive);

        $role = resolve(RoleRepositoryInterface::class)->roleOf($user);

        return $activePlacements->filter(function ($placement) use ($role) {
            if (null === $placement->allowed_user_roles) {
                return true;
            }

            if (!count($placement->allowed_user_roles)) {
                return true;
            }

            if (in_array($role->entityId(), $placement->allowed_user_roles)) {
                return true;
            }

            return false;
        })
            ->map(function ($placement) {
                return [
                    'label' => $placement->toTitle(),
                    'value' => $placement->entityId(),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function clearCaches(): void
    {
        localCacheStore()->deleteMultiple([self::PLACEMENTS_FOR_ADVERTISE_CACHE_ID]);
    }
}
