<?php

namespace MetaFox\Forum\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Forum\Models\Forum;
use MetaFox\Platform\Contracts\User;

interface ForumRepositoryInterface
{
    /**
     * @param User $context
     *
     * @return array
     */
    public function viewForums(User $context, array $attributes): array;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Forum
     */
    public function viewForum(User $context, int $id): Forum;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return array
     */
    public function getForumsForView(User $context, array $attributes = []): array;

    /**
     * @param User       $context
     * @param Forum|null $forum
     * @param bool       $filterClosed
     *
     * @return array
     */
    public function getForumsForForm(User $context, ?Forum $forum = null, bool $filterClosed = true): array;

    /**
     * @param  Forum|null $forum
     * @return array
     */
    public function getForumOptions(?Forum $forum = null): array;

    /**
     * @return array
     */
    public function getActiveForumIds(): array;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return ?Collection
     */
    public function getForumsForNavigation(User $context, array $attributes): ?Collection;

    /**
     * @param User $context
     * @param int  $parentId
     * @param int  $limit
     *
     * @return Collection|null
     */
    public function getSubForums(User $context, int $parentId, int $limit = 4): ?Paginator;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Paginator
     */
    public function getSearchItems(User $context, array $attributes): Paginator;

    /**
     * @param int  $forumId
     * @param bool $includeSelf
     *
     * @return array
     */
    public function getAscendantIds(int $forumId, bool $includeSelf = true): array;

    /**
     * @param int $forumId
     *
     * @return array
     */
    public function getDescendantIds(int $forumId): array;

    /**
     * @param int    $forumId
     * @param string $column
     * @param int    $total
     *
     * @return void
     */
    public function increaseTotal(int $forumId, string $column, int $total = 1): void;

    /**
     * @param int    $forumId
     * @param string $column
     * @param int    $total
     *
     * @return void
     */
    public function decreaseTotal(int $forumId, string $column, int $total = 1): void;

    /**
     * @param int $level
     *
     * @return void
     */
    public function migrateStatistics(int $level): void;

    /**
     * @param int $level
     *
     * @return void
     */
    public function migrateForumLevel(int $level = 1): void;

    /**
     * @param array $attributes
     *
     * @return Paginator
     */
    public function paginateForums(array $attributes = []): Paginator;

    /**
     * @param int $id
     *
     * @return bool
     */
    public function isClosed(int $id): bool;

    /**
     * @param array $attributes
     *
     * @return Builder
     */
    public function builderQueryForums(array $attributes): Builder;
}
