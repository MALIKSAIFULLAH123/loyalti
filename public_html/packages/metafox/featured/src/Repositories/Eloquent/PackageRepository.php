<?php

namespace MetaFox\Featured\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Repositories\ApplicableItemTypeRepositoryInterface;
use MetaFox\Featured\Repositories\ApplicableRoleRepositoryInterface;
use MetaFox\Featured\Support\Constants;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Featured\Repositories\PackageRepositoryInterface;
use MetaFox\Featured\Models\Package;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class PackageRepository.
 */
class PackageRepository extends AbstractRepository implements PackageRepositoryInterface
{
    public const SEARCH_PACKAGE_CACHE_ID = 'featured_package_search_options';

    public function model()
    {
        return Package::class;
    }

    public function createPackage(array $data): Package
    {
        /**
         * @var Package $package
         */
        $package = $this->getModel()->newInstance($data);

        $package->save();

        $this->handleApplicableItems($package, $data);

        localCacheStore()->delete(self::SEARCH_PACKAGE_CACHE_ID);

        LoadReduce::flush();

        return $package->refresh();
    }

    public function updatePackage(Package $package, array $data): Package
    {
        $package->fill($data);

        $package->save();

        $this->handleApplicableItems($package, $data);

        localCacheStore()->delete(self::SEARCH_PACKAGE_CACHE_ID);

        LoadReduce::flush();

        return $package->refresh();
    }

    public function deletePackage(Package $package): bool
    {
        $package->delete();

        localCacheStore()->delete(self::SEARCH_PACKAGE_CACHE_ID);

        LoadReduce::flush();

        return true;
    }

    public function viewAdmincpPackages(array $attributes = []): Paginator
    {
        $builder = $this->getModel()->newQuery();

        $limit   = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        if (Arr::has($attributes, 'title')) {
            $builder->where('featured_packages.title', $this->likeOperator(), '%' . $attributes['title'] . '%');
        }

        if (Arr::has($attributes, 'duration_period')) {
            $period = Arr::get($attributes, 'duration_period');

            match ($period) {
                null    => $builder->whereNull('featured_packages.duration_period'),
                default => $builder->where('featured_packages.duration_period', '=', $period),
            };
        }

        if (Arr::has($attributes, 'is_free')) {
            $builder->where('featured_packages.is_free', '=', (bool) Arr::get($attributes, 'is_free'));
        }

        if (Arr::has($attributes, 'is_active')) {
            $builder->where('featured_packages.is_active', '=', (bool) Arr::get($attributes, 'is_active'));
        }

        return $builder->paginate($limit, ['featured_packages.*']);
    }

    protected function handleApplicableItems(Package $package, array $data): void
    {
        resolve(ApplicableItemTypeRepositoryInterface::class)->updateForPackage($package, Arr::get($data, 'applicable_item_types') ?: []);

        resolve(ApplicableRoleRepositoryInterface::class)->updateForPackage($package, Arr::get($data, 'applicable_role_ids') ?: []);
    }

    public function getPackageOptionsForEntityType(User $user, string $entityType): array
    {
        return LoadReduce::remember(sprintf('featured::package::getPackageOptionsForEntityType(%s,%s)', $user->entityId(), $entityType), function () use ($user, $entityType) {
            $roleIds = $user->roles()->pluck('id')->toArray();

            $userCurrencyId = app('currency')->getUserCurrencyId($user);

            $itemBuilder = resolve(ApplicableItemTypeRepositoryInterface::class)->getModel()->newQuery()
                ->where('featured_applicable_item_types.item_type', '=', $entityType)
                ->select('featured_applicable_item_types.package_id');

            $roleBuilder = resolve(ApplicableRoleRepositoryInterface::class)->getModel()->newQuery()
                ->whereIn('featured_applicable_roles.role_id', $roleIds)
                ->select('featured_applicable_roles.package_id');

            $packages = $this->getModel()->newQuery()
                ->where('featured_packages.is_active', '=', 1)
                ->where(function (Builder $builder) use ($itemBuilder) {
                    $builder->where('featured_packages.applicable_item_type', '=', Constants::ITEM_APPLICABLE_SCOPE_ALL)
                        ->orWhereIn('featured_packages.id', $itemBuilder);
                })
                ->where(function (Builder $builder) use ($roleBuilder) {
                    $builder->where('featured_packages.applicable_role_type', '=', Constants::USER_ROLE_APPLICABLE_SCOPE_ALL)
                        ->orWhereIn('featured_packages.id', $roleBuilder);
                })
                ->get();

            if (!$packages->count()) {
                return [];
            }

            return $packages->filter(function (Package $package) use ($user) {
                if ($package->is_free) {
                    return true;
                }

                $price = $package->getPriceForUser($user);

                if (!is_numeric($price)) {
                    return false;
                }

                return true;
            })->values()->map(function (Package $package) use ($user, $userCurrencyId) {
                $price = $package->getPriceForUser($user);

                $format = Feature::getPriceFormatted($price, $userCurrencyId);

                $title = sprintf('%s - %s (%s)', $package->toTitle(), $format, __p('featured::phrase.duration_for_package', [
                    'duration' => Feature::getDurationText($package->duration_period, $package->duration_value),
                ]));

                return [
                    'label' => $title,
                    'value' => $package->entityId(),
                ];
            })->toArray();
        });
    }

    public function getPackageById(int $packageId): ?Package
    {
        return LoadReduce::remember(sprintf('featured::package::getPackageById(%s)', $packageId), function () use ($packageId) {
            return $this->getModel()->newQuery()
                ->withTrashed()
                ->where('id', '=', $packageId)
                ->first();
        });
    }

    public function increasePackageTotalActive(int $packageId): void
    {
        $package = $this->getPackageById($packageId);

        if (!$package instanceof Package) {
            return;
        }

        $package->incrementAmount('total_active');

        LoadReduce::flush();
    }

    public function increasePackageTotalEnd(int $packageId): void
    {
        $package = $this->getPackageById($packageId);

        if (!$package instanceof Package) {
            return;
        }

        $package->decrementAmount('total_active');
        $package->incrementAmount('total_end');

        LoadReduce::flush();
    }

    public function increasePackageTotalCancelled(int $packageId): void
    {
        $package = $this->getPackageById($packageId);

        if (!$package instanceof Package) {
            return;
        }

        $package->decrementAmount('total_active');
        $package->incrementAmount('total_cancelled');

        LoadReduce::flush();
    }

    public function decreasePackageTotalActive(int $packageId): void
    {
        $package = $this->getPackageById($packageId);

        if (!$package instanceof Package) {
            return;
        }

        $package->decrementAmount('total_active');

        LoadReduce::flush();
    }

    public function decreasePackageTotalEnd(int $packageId): void
    {
        $package = $this->getPackageById($packageId);

        if (!$package instanceof Package) {
            return;
        }

        $package->decrementAmount('total_end');

        LoadReduce::flush();
    }

    public function decreasePackageTotalCancelled(int $packageId): void
    {
        $package = $this->getPackageById($packageId);

        if (!$package instanceof Package) {
            return;
        }

        $package->decrementAmount('total_cancelled');

        LoadReduce::flush();
    }

    /**
     * @return array
     */
    public function getPackageSearchOptions(): array
    {
        return localCacheStore()->rememberForever(self::SEARCH_PACKAGE_CACHE_ID, function () {
            return $this->getModel()->newQuery()
                ->get()
                ->map(function (Package $package) {
                    return [
                        'label' => $package->toTitle(),
                        'value' => $package->entityId(),
                    ];
                })
                ->toArray();
        });
    }

    public function viewPackagesForSearch(User $context, array $attributes = []): Collection
    {
        $builder = $this->getModel()->newQuery();

        $q = Arr::get($attributes, 'q');

        if (is_string($q)) {
            $builder->where('title', $this->likeOperator(), '%' . $q . '%');
        }

        return $builder
            ->get();
    }
}
