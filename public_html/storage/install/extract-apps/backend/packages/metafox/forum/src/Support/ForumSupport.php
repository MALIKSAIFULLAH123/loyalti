<?php

namespace MetaFox\Forum\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Forum\Contracts\ForumSupportContract;
use MetaFox\Forum\Http\Resources\v1\Forum\ForumSideBlockItemCollection;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Forum\Repositories\UserRolePermissionRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class ForumSupport implements ForumSupportContract
{
    public const IS_CLOSED = 1;
    public const IS_OPEN   = 0;

    public const NAVIGATION_CACHE_ID  = 'forums_navigation';
    public const VIEW_CACHE_ID        = 'forums_view';
    public const VIEW_MOBILE_CACHE_ID = 'forums_view_mobile';
    public const FORM_CACHE_ID        = 'forums_form';

    public const VIEW_SUB_FORUMS       = 'sub_forums';
    public const VIEW_QUICK_NAVIGATION = 'quick_navigation';

    public const SEARCH_BY_THREAD = ForumThread::ENTITY_TYPE;
    public const SEARCH_BY_POST   = ForumPost::ENTITY_TYPE;

    public const UPDATE_TITLE_ACTION       = 'update_title';
    public const UPDATE_DESCRIPTION_ACTION = 'update_description';
    public const UPDATE_INFO_ACTION        = 'update_info';
    public const MOVE_ACTION               = 'move';
    public const CLOSE_ACTION              = 'close';
    public const REOPEN_ACTION             = 'reopen';

    public const STICK_ACTION = 'stick';

    public const TOTAL_ALL_THREADS_CACHE_ID = 'forum_total_all_threads_';
    public const TOTAL_ALL_SUBS_CACHE_ID    = 'forum_total_all_subs_';

    public const CAPTCHA_RULE_CREATE_THREAD = 'create_thread';
    public const CAPTCHA_RULE_CREATE_POST   = 'create_post';

    public const DELETE_PERMANENTLY = 'permanently';
    public const DELETE_MIGRATION   = 'migration';

    public const MAX_FORUM_TITLE_LEMGTH = 255;

    protected $repository;

    public function __construct(
        ForumRepositoryInterface $repository,
        protected ForumThreadRepositoryInterface $threadRepository,
        protected UserRolePermissionRepositoryInterface $userRolePermissionRepository,
    ) {
        $this->repository = $repository;
    }

    public function clearCaches(int $id = 0): void
    {
        $caches = [self::NAVIGATION_CACHE_ID, self::VIEW_CACHE_ID];

        localCacheStore()->deleteMultiple($caches);

        if ($id > 0) {
            localCacheStore()->deleteMultiple([
                self::TOTAL_ALL_THREADS_CACHE_ID . $id, self::TOTAL_ALL_SUBS_CACHE_ID . $id,
            ]);
        }
    }

    public function getViewCacheId(): string
    {
        return self::VIEW_CACHE_ID;
    }

    public function getViewMobileCacheId(): string
    {
        return self::VIEW_MOBILE_CACHE_ID;
    }

    public function getFormCacheId(): string
    {
        return self::FORM_CACHE_ID;
    }

    public function getActiveForumIdsCacheId(): string
    {
        return 'get_active_forum_ids_' . config('localize.view_mode', 'view');
    }

    public function getClosedStatus(): int
    {
        return self::IS_CLOSED;
    }

    public function buildForumIdsForSearch(int $id): array
    {
        $ids = [$id];

        $items = Forum::query()
            ->withTrashed()
            ->where([
                'parent_id' => $id,
            ])
            ->get()
            ->collect();

        if (null !== $items) {
            foreach ($items as $item) {
                $itemId = $item->entityId();
                $ids    = array_merge($ids, $this->buildForumIdsForSearch($itemId));
            }
        }

        return $ids;
    }

    public function getOpenStatus(): int
    {
        return self::IS_OPEN;
    }

    public function buildForumsForForm(int $parentId = 0): array
    {
        $status = $this->getOpenStatus();

        $items = Forum::query()
            ->where([
                'is_closed' => $status,
                'parent_id' => $parentId,
            ])
            ->orderBy('ordering')
            ->get()
            ->collect();

        $forums = [];

        foreach ($items as $item) {
            /* @var Forum $item */
            $forums[] = [
                'label'   => parse_output()->parse($item->title),
                'value'   => $item->entityId(),
                'options' => $this->buildForumsForForm($item->entityId()),
            ];
        }

        return $forums;
    }

    public function buildForumsForNavigation(int $parentId = 0, array $attributes = []): ?Collection
    {
        return Forum::query()
            ->where([
                'parent_id' => $parentId,
            ])
            ->orderBy('ordering')
            ->get();
    }

    public function buildTotalThreadsForNavigation(int $id, int $total = 0): int
    {
        $items = Forum::query()
            ->where([
                'parent_id' => $id,
            ])
            ->get()
            ->collect();

        foreach ($items as $item) {
            $total += $this->buildTotalThreadsForNavigation($item->entityId(), $item->total_thread);
        }

        return $total;
    }

    /**
     * @inheritDoc
     */
    public function buildTotalSubsForNavigation(int $id): int
    {
        $items = Forum::query()
            ->where([
                'parent_id' => $id,
            ])
            ->get()
            ->collect();

        $total = count($items);

        foreach ($items as $item) {
            $total += $this->buildTotalSubsForNavigation($item->entityId());
        }

        return $total;
    }

    /**
     * @inheritDoc
     */
    public function getNavigationCacheId(): string
    {
        return self::NAVIGATION_CACHE_ID;
    }

    public function buildForumsForView(int $parentId = 0): array
    {
        $items = Forum::query()
            ->with(['subForums'])
            ->where([
                'parent_id' => $parentId,
            ])
            ->orderBy('ordering')
            ->get();

        if (null === $items) {
            return [];
        }
        $resources = $this->getResourceItem($items);

        foreach ($resources as $key => $resource) {
            if (!$this->userRolePermissionRepository->hasAccess(user()->roleId(), $resource['id'], 'can_view_forum')){
                unset($resources[$key]);
                continue;
            }
            $resources[$key]['subs'] = $this->buildForumsForView($resource['id']);
        }
        $resources = array_values($resources);

        return $resources;
    }

    protected function getResourceItem(Collection $items): array
    {
        $request = resolve(Request::class);

        $collection = new ForumSideBlockItemCollection($items);

        return $collection->toArray($request);
    }

    public function buildForumsForViewMobile(): array
    {
        $items = Forum::query()
            ->with(['subForums'])
            ->where('parent_id', '=', 0)
            ->orderBy('ordering')
            ->get();

        $items = $items->filter(function ($item) {
            if (!$this->userRolePermissionRepository->hasAccess(user()->roleId(), $item->entityId(), 'can_view_forum')){
                return false;
            }
            $item->subForums = $item->subForums->filter(function ($subItem) {
                if (!$this->userRolePermissionRepository->hasAccess(user()->roleId(), $subItem->entityId(), 'can_view_forum')){
                    return false;
                }
                return true;
            });
            return true;
        });

        return $this->getResourceItem($items);
    }

    public function updateAttachments(Content $item, ?array $attachments = []): void
    {
        if (is_array($attachments)) {
            resolve(AttachmentRepositoryInterface::class)->updateItemId($attachments, $item);
        }
    }

    public function getModuleName(): string
    {
        return 'forum';
    }

    public function getItemTypesForSearch(): array
    {
        return [self::SEARCH_BY_THREAD, self::SEARCH_BY_POST];
    }

    public function getTotalAllThreads(int $id): int
    {
        $cacheId = self::TOTAL_ALL_THREADS_CACHE_ID . $id;

        return localCacheStore()->rememberForever($cacheId, function () use ($id) {
            $forum = $this->repository->find($id);

            return $this->buildTotalThreadsForNavigation($id, $forum->total_thread);
        });
    }

    public function getTotalAllSubs(int $id): int
    {
        $cacheId = self::TOTAL_ALL_SUBS_CACHE_ID . $id;

        return localCacheStore()->rememberForever($cacheId, function () use ($id) {
            return $this->buildTotalSubsForNavigation($id);
        });
    }

    /**
     * @inheritDoc
     */
    public function getTotalRepliesAllSubs(int $id): int
    {
        $status = $this->getOpenStatus();

        $items = Forum::query()
            ->where([
                'parent_id' => $id,
            ])
            ->get()
            ->collect();

        $total = $this->getTotalRepliesThread($id);

        foreach ($items as $item) {
            $total += $this->getTotalRepliesThread($item->entityId());
        }

        return $total;
    }

    protected function getTotalRepliesThread(int $forumId): int
    {
        $instance = $this->threadRepository->getModel()->newModelQuery();
        $items    = $instance
            ->where('forum_id', $forumId)
            ->get()
            ->collect();

        return $items->sum('total_comment');
    }
}
