<?php

namespace MetaFox\Giphy\Repositories;

use MetaFox\Platform\Contracts\User;

interface GifRepositoryInterface
{
    /**
     * @param  User  $user
     * @param  array $attributes
     * @return array
     */
    public function search(User $user, array $attributes): array;

    /**
     * @param  User  $user
     * @param  array $attributes
     * @return array
     */
    public function trending(User $user, array $attributes): array;

    /**
     * @param  User       $user
     * @param  string     $id
     * @return array|null
     */
    public function getGifData(User $user, string $id): ?array;
}
