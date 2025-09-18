<?php

namespace MetaFox\GettingStarted\Repositories;

use MetaFox\Platform\Contracts\User;

interface TodoListImageRepositoryInterface
{
    /**
     * @param  User       $context
     * @param  int        $id
     * @param  array|null $attachedPhotos
     * @param  bool       $isUpdated
     * @return bool
     */
    public function updateImages(User $context, int $id, ?array $attachedPhotos, bool $isUpdated = true): bool;
}
