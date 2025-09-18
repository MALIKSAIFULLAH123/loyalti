<?php

namespace MetaFox\Marketplace\Repositories;

use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ImageRepositoryInterface.
 * @mixin BaseRepository
 */
interface ImageRepositoryInterface
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
