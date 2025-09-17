<?php

namespace MetaFox\User\Repositories;

use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface UserPreference.
 *
 * @mixin BaseRepository
 */
interface UserPreferenceRepositoryInterface
{
    /**
     * Update user preferences or create a new one if not exist.
     *
     * @param  User                 $user
     * @param  array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function updateOrCreatePreferences(User $user, array $attributes = []): array;

    /**
     * Get all current user preferences.
     *
     * @param  User                 $user
     * @return array<string, mixed>
     */
    public function getPreferences(User $user): array;
}
