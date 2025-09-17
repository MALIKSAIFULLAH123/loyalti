<?php

namespace MetaFox\User\Repositories\Eloquent;

use Illuminate\Support\Facades\Cache;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\UserPreference as Model;
use MetaFox\User\Repositories\UserPreferenceRepositoryInterface;
use MetaFox\User\Support\CacheManager;

/**
 * Class UserPreferenceRepository.
 * @method Model getModel()
 */
class UserPreferenceRepository extends AbstractRepository implements UserPreferenceRepositoryInterface
{
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     */
    public function updateOrCreatePreferences(User $user, array $attributes = []): array
    {
        foreach ($attributes as $key => $value) {
            $type = gettype($value);

            $value = match ($type) {
                'array'   => json_encode($value),
                'boolean' => $value ? '1' : '0',
                default   => parse_input()->clean($value, true, false),
            };

            $this->getModel()->newQuery()->updateOrCreate([
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'name'      => $key,
            ], [
                'type'  => $type,
                'value' => $value,
            ]);

            $cacheKey = sprintf(CacheManager::USER_PREFERENCE_VALUE_BY_NAME_CACHE, $key, $user->entityType(), $user->entityId());

            Cache::forget($cacheKey);
        }

        LoadReduce::flush();

        return $this->getPreferences($user);
    }

    /**
     * @inheritDoc
     */
    public function getPreferences(User $user): array
    {
        return $this->getModel()
            ->newQuery()
            ->where('user_type', $user->entityType())
            ->where('user_id', $user->entityId())
            ->get()
            ->collect()
            ->pluck('value', 'name')
            ->toArray();
    }
}
