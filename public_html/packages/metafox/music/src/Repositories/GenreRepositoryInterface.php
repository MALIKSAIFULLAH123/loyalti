<?php

namespace MetaFox\Music\Repositories;

use MetaFox\Music\Models\Genre;
use MetaFox\Music\Models\GenreRelation;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\Contracts\CategoryRepositoryInterface as PlatformRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface GenreRepositoryInterface.
 * @mixin BaseRepository
 */
interface GenreRepositoryInterface extends PlatformRepositoryInterface
{
    public function deleteCategory(User $context, int $id, int $newCategoryId): bool;

    /**
     * @return Genre|null
     */
    public function getCategoryDefault(): ?Genre;

    /**
     * @return array
     */
    public function getDefaultCategoryParentIds(): array;

    public function getRelationModel(): GenreRelation;
}
