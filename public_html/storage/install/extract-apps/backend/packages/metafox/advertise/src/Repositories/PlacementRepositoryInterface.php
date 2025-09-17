<?php

namespace MetaFox\Advertise\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Advertise\Models\Placement;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User;

/**
 * Interface Placement.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface PlacementRepositoryInterface
{
    /**
     * @param  User       $context
     * @param  array      $attributes
     * @return Collection
     */
    public function viewPlacementsInAdminCP(User $context, array $attributes = []): Collection;
    /**
     * @param  User           $context
     * @param  array          $attributes
     * @return Placement|null
     */
    public function createPlacement(User $context, array $attributes): ?Placement;

    /**
     * @param  User           $context
     * @param  int            $id
     * @param  array          $attributes
     * @return Placement|null
     */
    public function updatePlacement(User $context, int $id, array $attributes): ?Placement;

    /**
     * @param  User     $context
     * @param  int      $id
     * @param  string   $deleteOption
     * @param  int|null $alternativeId
     * @return bool
     */
    public function deletePlacement(User $context, int $id, string $deleteOption, ?int $alternativeId = null): bool;

    /**
     * @param  int   $deletedId
     * @return array
     */
    public function getMigrationOptions(int $deletedId): array;

    /**
     * @return array
     */
    public function getActivePlacementsForSetting(): array;

    /**
     * @param  User       $context
     * @param  bool       $isFree
     * @param  bool|null  $isActive
     * @return Collection
     */
    public function getPlacementsForAdvertise(User $context, bool $isFree = false, ?bool $isActive = true): Collection;

    /**
     * @param  User $context
     * @param  int  $id
     * @param  bool $isActive
     * @return bool
     */
    public function activePlacement(User $context, int $id, bool $isActive): bool;

    /**
     * @param  int        $placementId
     * @param  string     $currencyId
     * @return float|null
     */
    public function getPlacementPriceByCurrencyId(int $placementId, string $currencyId): ?float;

    /**
     * @param  User      $user
     * @param  bool|null $isActive
     * @return array
     */
    public function getAvailablePlacements(User $user, ?bool $isActive = true): array;
}
