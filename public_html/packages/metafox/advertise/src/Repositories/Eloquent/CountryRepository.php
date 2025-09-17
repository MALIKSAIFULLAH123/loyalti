<?php

namespace MetaFox\Advertise\Repositories\Eloquent;

use Illuminate\Support\Facades\Cache;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Advertise\Repositories\CountryRepositoryInterface;
use MetaFox\Advertise\Models\Country;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class CountryRepository.
 */
class CountryRepository extends AbstractRepository implements CountryRepositoryInterface
{
    public const LOCATION_CACHE_ID = 'advertise_location_';

    public function model()
    {
        return Country::class;
    }

    public function createLocation(Entity $entity, ?array $locations): void
    {
        $query = Country::query()
            ->where([
                'item_type' => $entity->entityType(),
                'item_id'   => $entity->entityId(),
            ]);

        if (null === $locations) {
            $query->delete();

            return;
        }

        $locations = array_unique($locations);

        $current = $this->getLocations($entity);

        $deleted = array_diff($current, $locations);

        $inserted = array_diff($locations, $current);

        if (count($deleted)) {
            $query->clone()->whereIn('country_code', $deleted)->delete();
        }

        if ($inserted) {
            $inserted = array_map(function ($value) use ($entity) {
                return [
                    'item_id'      => $entity->entityId(),
                    'item_type'    => $entity->entityType(),
                    'country_code' => $value,
                ];
            }, $inserted);

            $query->clone()->insert($inserted);
        }

        $this->clearCaches($entity);
    }

    public function deleteLocations(Entity $entity): void
    {
        Country::query()
            ->where([
                'item_type' => $entity->entityType(),
                'item_id'   => $entity->entityId(),
            ])
            ->delete();

        $this->clearCaches($entity);
    }

    protected function clearCaches(Entity $entity): void
    {
        Cache::delete($this->getCacheId($entity));
    }

    protected function getCacheId(Entity $entity): string
    {
        return self::LOCATION_CACHE_ID . $entity->entityType() . '_' . $entity->entityId();
    }

    public function getLocations(Entity $entity): array
    {
        return Cache::remember($this->getCacheId($entity), 3600, function () use ($entity) {
            return Country::query()
                ->where([
                    'item_id'   => $entity->entityId(),
                    'item_type' => $entity->entityType(),
                ])
                ->get()
                ->pluck('country_code')
                ->toArray();
        });
    }
}
