<?php

namespace MetaFox\Profile\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Profile\Models\Section;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Section.
 *
 * @mixin BaseRepository
 * * @method Section getModel()
 */
interface SectionRepositoryInterface
{
    /**
     * @return array
     */
    public function getAllSectionForForm(): array;

    /**
     * @param string $type
     * @return array
     */
    public function getSectionByTypeForForm(string $type): array;

    /**
     * @param User  $user
     * @param array $attribute
     * @return Paginator
     */
    public function viewSections(User $user, array $attribute): Paginator;

    /**
     * @param User  $user
     * @param array $attribute
     * @return bool
     */
    public function deleteOrMoveToNewSection(User $user, array $attribute): bool;

    /**
     * @param array<int> $orderIds
     * @return bool
     */
    public function orderSections(array $orderIds): bool;

    /**
     * @param int   $id
     * @param array $attributes
     * @return Section
     */
    public function toggleActive(int $id, array $attributes): Section;

    /**
     * @param array $attributes
     * @return Section
     */
    public function createSection(array $attributes): Section;

    /**
     * @param User  $user
     * @param array $attribute
     * @return Builder
     */
    public function buildQuerySection(User $user, array $attribute): Builder;

    /**
     * @param Builder $query
     * @param array   $attribute
     * @return Builder
     */
    public function buildQueryProfile(Builder $query, array $attribute): Builder;

    /**
     * @param string $name
     * @return Section|null
     */
    public function getSectionByName(string $name): ?Section;
}
