<?php

namespace MetaFox\Search\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Search.
 *
 * @mixin BaseRepository
 * @method Builder terms(string $terms)
 */
interface SearchRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $params
     *
     * @return Collection
     */
    public function searchItems(User $context, array $params): array;

    /**
     * @param  HasGlobalSearch $item
     * @return void
     */
    public function createdBy(HasGlobalSearch $item): void;

    /**
     * @param  HasGlobalSearch $item
     * @return void
     */
    public function updatedBy(HasGlobalSearch $item): void;

    /**
     * @param  HasGlobalSearch $item
     * @return void
     */
    public function deletedBy(HasGlobalSearch $item): void;

    /**
     * @param  User                                     $user
     * @param  array<string, mixed>                     $params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSuggestion(User $user, array $params = []): Collection;

    /**
     * @param  User       $context
     * @param  array      $attributes
     * @return Collection
     */
    public function getGroups(User $context, array $attributes = []): Collection;

    /**
     * @param  User    $context
     * @param  array   $attributes
     * @return Builder
     */
    public function buildQuery(User $context, array $attributes = []): Builder;

    /**
     * @param  string $itemType
     * @param  int    $itemId
     * @param  array  $attributes
     * @return bool
     */
    public function updateSearchText(string $itemType, int $itemId, array $attributes): bool;

    /**
     * @return array
     */
    public function getWhenOptions(): array;

    /**
     * @param  HasGlobalSearch $item
     * @return bool
     */
    public function deletedByItem(string $itemType, int $itemId): bool;

    /**
     * @param  array     $attributes
     * @return Paginator
     */
    public function getTrendingHashtags(array $attributes = []): Paginator;

    /**
     * @return void
     */
    public function reindex(): void;
}
