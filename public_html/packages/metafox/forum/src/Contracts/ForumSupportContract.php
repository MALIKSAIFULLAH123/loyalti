<?php

namespace MetaFox\Forum\Contracts;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Platform\Contracts\Content;

interface ForumSupportContract
{
    /**
     * @return void
     */
    public function clearCaches(int $id = 0): void;

    /**
     * @return string
     */
    public function getViewCacheId(): string;

    /**
     * @return string
     */
    public function getViewMobileCacheId(): string;

    /**
     * @return string
     */
    public function getFormCacheId(): string;

    /**
     * @return string
     */
    public function getNavigationCacheId(): string;

    /**
     * @return string
     */
    public function getActiveForumIdsCacheId(): string;

    /**
     * @return int
     */
    public function getOpenStatus(): int;

    /**
     * @return int
     */
    public function getClosedStatus(): int;

    /**
     * @param  int   $id
     * @return array
     */
    public function buildForumIdsForSearch(int $id): array;

    /**
     * @param  int   $parentId
     * @return array
     */
    public function buildForumsForForm(int $parentId = 0): array;

    /**
     * @param  int $id
     * @param  int $total
     * @return int
     */
    public function buildTotalThreadsForNavigation(int $id, int $total = 0): int;

    /**
     * @param  int $id
     * @return int
     */
    public function buildTotalSubsForNavigation(int $id): int;

    /**
     * @param  int       $parentId
     * @param  array     $attributes
     * @return Paginator
     */
    public function buildForumsForNavigation(int $parentId = 0, array $attributes = []): ?Collection;

    /**
     * @param  int             $parentId
     * @return Collection|null
     */
    public function buildForumsForView(int $parentId = 0): array;

    /**
     * @return array
     */
    public function buildForumsForViewMobile(): array;

    /**
     * @param  Content    $item
     * @param  array|null $attachments
     * @return void
     */
    public function updateAttachments(Content $item, ?array $attachments = []): void;

    /**
     * @return string
     */
    public function getModuleName(): string;

    /**
     * @return array
     */
    public function getItemTypesForSearch(): array;

    /**
     * @param  int $id
     * @return int
     */
    public function getTotalAllThreads(int $id): int;

    /**
     * @param  int $id
     * @return int
     */
    public function getTotalAllSubs(int $id): int;

    /**
     * @param  int $id
     * @return int
     */
    public function getTotalRepliesAllSubs(int $id): int;
}
