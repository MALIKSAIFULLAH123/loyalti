<?php

namespace MetaFox\Forum\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait;
 */
interface ForumThreadRepositoryInterface extends HasSponsor, HasSponsorInFeed
{
    /**
     * @param  User      $context
     * @param  User      $owner
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewThreads(User $context, User $owner, array $attributes = []): Paginator;

    /**
     * @param  User  $context
     * @param  int   $id
     * @return mixed
     */
    public function viewThread(User $context, int $id): ForumThread;

    /**
     * @param  User  $context
     * @param  User  $owner
     * @param  array $attributes
     * @return mixed
     */
    public function createThread(User $context, User $owner, array $attributes): ForumThread;

    /**
     * @param  ForumThread $thread
     * @param  string      $itemType
     * @param  array       $attributes
     * @return void
     */
    public function createIntegratedItem(ForumThread $thread, string $itemType, array $attributes): void;

    /**
     * @param  User        $context
     * @param  ForumThread $thread
     * @param  string|null $itemType
     * @param  int         $itemId
     * @param  array       $attributes
     * @return void
     */
    public function updateIntegratedItem(User $context, ForumThread $thread, ?string $itemType, int $itemId, array $attributes): void;

    /**
     * @param  User        $context
     * @param  int         $id
     * @param  array       $attributes
     * @return ForumThread
     */
    public function updateThread(User $context, int $id, array $attributes): ForumThread;

    /**
     * @param  User  $context
     * @param  int   $id
     * @return mixed
     */
    public function deleteThread(User $context, int $id): bool;

    /**
     * @param  ForumThread $thread
     * @return void
     */
    public function increaseTotalView(ForumThread $thread): void;

    /**
     * @param  User $context
     * @param  int  $id
     * @param  bool $isSubscribed
     * @param  bool $checkPermission
     * @return void
     */
    public function subscribeThread(User $context, int $id, bool $isSubscribed, bool $checkPermission = false): void;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param  int         $newForumId
     * @param  int         $oldForumId
     * @param  ForumThread $thread
     * @return void
     */
    public function updateForumTotal(int $newForumId, int $oldForumId, ForumThread $thread): void;

    /**
     * @param  User $context
     * @param  int  $id
     * @param  int  $forumId
     * @return bool
     */
    public function move(User $context, int $id, int $forumId): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @param  bool $isSticked
     * @return bool
     */
    public function stick(User $context, int $id, bool $isSticked): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @param  bool $isClosed
     * @return bool
     */
    public function close(User $context, int $id, bool $isClosed): bool;

    /**
     * @param  User    $context
     * @param  User    $owner
     * @param  array   $attributes
     * @return Builder
     */
    public function buildQueryForListing(User $context, User $owner, array $attributes): Builder;

    /**
     * @param  User             $context
     * @param  array            $attributes
     * @return ForumThread|null
     */
    public function copy(User $context, array $attributes): ?ForumThread;

    /**
     * @param  User $context
     * @param  int  $threadId
     * @param  int  $postId
     * @return bool
     */
    public function updateLastRead(User $context, int $threadId, int $postId): bool;

    /**
     * @param  User  $context
     * @param  array $attributes
     * @return array
     */
    public function merge(User $context, array $attributes): array;

    /**
     * @param  User             $context
     * @param  string           $actionType
     * @param  ForumThread|null $thread
     * @param  array|null       $actionValue
     * @param  Collection|null  $subscribers
     * @return void
     */
    public function sendNotificationForThreadSubscription(User $context, string $actionType, ?ForumThread $thread, ?array $actionValue = null, ?Collection $subscribers = null): void;

    /**
     * @param  User        $context
     * @param  ForumThread $thread
     * @return ForumThread
     */
    public function processAfterViewDetail(User $context, ForumThread $thread): ForumThread;

    /**
     * @param  ForumThread $thread
     * @return void
     */
    public function updatePostId(ForumThread $thread): void;
}
