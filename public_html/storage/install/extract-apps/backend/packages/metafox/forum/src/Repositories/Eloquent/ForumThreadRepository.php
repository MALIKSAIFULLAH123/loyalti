<?php

namespace MetaFox\Forum\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Forum\Jobs\SubscribedThread;
use MetaFox\Forum\Jobs\UpdateWiki;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Notifications\AdminUpdateThread;
use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadLastReadRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadSubscribeRepositoryInterface;
use MetaFox\Forum\Support\Browse\Scopes\ForumScope;
use MetaFox\Forum\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadSortScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewScope as ViewScope;
use MetaFox\Forum\Support\Facades\Forum as ForumFacade;
use MetaFox\Forum\Support\Facades\ForumThread as ForumThreadFacade;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\ResourcePermission;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\TagScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

class ForumThreadRepository extends AbstractRepository implements ForumThreadRepositoryInterface
{
    use HasApprove;
    use HasSponsor;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';

    public function model()
    {
        return ForumThread::class;
    }

    /**
     * @param  User                   $context
     * @param  User                   $owner
     * @param  array                  $attributes
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewThreads(User $context, User $owner, array $attributes = []): Paginator
    {
        $profileId = $attributes['user_id'];

        if ($context->entityId() > 0 && $context->entityId() == $profileId && Arr::get($attributes, 'view') != Browse::VIEW_PENDING) {
            Arr::set($attributes, 'view', Browse::VIEW_MY);
        }

        $view = $attributes['view'];

        switch ($view) {
            case Browse::VIEW_PENDING:
                if ($profileId == 0 || $profileId != $context->entityId()) {
                    policy_authorize(ForumThreadPolicy::class, 'approve', $context);
                }
                break;
            case ThreadViewScope::VIEW_MERGED:
                policy_authorize(ForumThreadPolicy::class, 'merge', $context);
                break;
            default:
                policy_authorize(ForumThreadPolicy::class, 'viewAny', $context, $owner);
                break;
        }

        $this->withUserMorphTypeActiveScope();

        $query = $this->buildQueryForListing($context, $owner, $attributes);

        $this->applySetting($query, $owner, $view);

        $relations = ForumThreadFacade::getRelations();

        return $query
            ->with($relations)
            ->paginate($attributes['limit'], ['forum_threads.*']);
    }

    public function viewThread(User $context, int $id): ForumThread
    {
        $relations = ForumThreadFacade::getRelations();

        $thread = $this
            ->withUserMorphTypeActiveScope()
            ->with($relations)
            ->find($id);

        policy_authorize(ForumThreadPolicy::class, 'view', $context, $thread);

        return $thread;
    }

    public function createThread(User $context, User $owner, array $attributes): ForumThread
    {
        policy_authorize(ForumThreadPolicy::class, 'createOnForum', $context, $owner, Arr::get($attributes, 'forum_id'));

        $isSubscribed = $attributes['is_subscribed'];

        $integratedItem = $attributes['integrated_item'];

        $itemType = $attributes['item_type'];

        unset($attributes['is_subscribed'], $attributes['integrated_item']);

        $attributes = array_merge($attributes, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'owner_id'    => $owner->entityId(),
            'owner_type'  => $owner->entityType(),
            'item_type'   => null,
            'item_id'     => 0,
            'is_approved' => (int) policy_check(ForumThreadPolicy::class, 'autoApprove', $context, $owner),
        ]);

        $thread = new ForumThread($attributes);
        if (!$thread->isApproved() && is_array($integratedItem) && count($integratedItem) > 0) {
            $integratedItem['prohibited_approval'] = true;
        }

        $thread->save();

        if (array_key_exists('attachments', $attributes) && null !== $attributes['attachments']) {
            ForumFacade::updateAttachments($thread, $attributes['attachments']);
        }

        if (null !== $itemType && is_array($integratedItem) && count($integratedItem) > 0) {
            $this->createIntegratedItem($thread, $itemType, $integratedItem);
        }

        resolve(ForumThreadLastReadRepositoryInterface::class)->updateLastRead($thread->entityId(), 0, $context);

        $this->subscribeThread($context, $thread->entityId(), $isSubscribed);

        $thread->refresh();

        return $thread;
    }

    public function createIntegratedItem(ForumThread $thread, string $itemType, array $attributes): void
    {
        $id = app('events')->dispatch(
            'forum.thread.integrated_item.create',
            [$thread->user, $thread->owner, $itemType, $attributes],
            true
        );

        if (is_numeric($id)) {
            $thread->update([
                'item_type' => $itemType,
                'item_id'   => $id,
            ]);
        }
    }

    protected function getActionTypesForUpdateIntegrated(
        User $context,
        ForumThread $thread,
        ?string $itemType,
        int $itemId,
        array $attributes
    ): array {
        $hasAttributes = count($attributes) > 0;

        $hasItemBefore = $itemId > 0;

        $actionTypes = [];

        $user = $context;

        switch ($hasItemBefore) {
            case true:
                $integratedItem = app('events')->dispatch(
                    'forum.thread.integrated_item.edit_initialize',
                    [$context, $itemType, $itemId, 'forum_thread.attach_poll'],
                    true
                );

                $hasId = Arr::has($attributes, 'id');

                if (is_array($integratedItem) && count($integratedItem) > 0) {
                    if (
                        Arr::has($integratedItem['permissions'], ResourcePermission::CAN_EDIT)
                        && $integratedItem['permissions'][ResourcePermission::CAN_EDIT]
                        && $hasAttributes
                        && $hasId
                    ) {
                        Arr::set($actionTypes, self::ACTION_UPDATE, [$user, $itemType, $itemId, $attributes]);
                    }

                    if (Arr::has($integratedItem['permissions'], ResourcePermission::CAN_DELETE)) {
                        if (!$hasAttributes || !$hasId) {
                            Arr::set($actionTypes, self::ACTION_DELETE, [$user, $itemType, $itemId]);
                        }
                        if ($hasAttributes && !$hasId) {
                            Arr::set($actionTypes, self::ACTION_CREATE, [$thread, $itemType, $attributes]);
                        }
                    }
                }

                break;
            default:
                if ($hasAttributes) {
                    Arr::set($actionTypes, self::ACTION_CREATE, [$thread, $itemType, $attributes]);
                }

                break;
        }

        return $actionTypes;
    }

    protected function executeActionTypesForUpdateIntegratedItem(ForumThread $thread, array $actionTypes): void
    {
        foreach ($actionTypes as $actionType => $actionValue) {
            switch ($actionType) {
                case self::ACTION_UPDATE:
                    app('events')->dispatch('forum.thread.integrated_item.update', $actionValue, true);
                    break;
                case self::ACTION_CREATE:
                    $this->createIntegratedItem(...$actionValue);
                    break;
                case self::ACTION_DELETE:
                    $success = app('events')->dispatch('forum.thread.integrated_item.delete', $actionValue, true);

                    if ($success) {
                        $thread->update([
                            'item_type' => null,
                            'item_id'   => 0,
                        ]);
                    }

                    break;
            }
        }
    }

    public function updateIntegratedItem(
        User $context,
        ForumThread $thread,
        ?string $itemType,
        int $itemId,
        array $attributes
    ): void {
        if (null === $itemType) {
            return;
        }

        $actionTypes = $this->getActionTypesForUpdateIntegrated($context, $thread, $itemType, $itemId, $attributes);

        if (count($actionTypes) > 0) {
            $this->executeActionTypesForUpdateIntegratedItem($thread, $actionTypes);
        }
    }

    public function subscribeThread(User $context, int $id, bool $isSubscribed, bool $checkPermission = false): void
    {
        $thread = $this->withUserMorphTypeActiveScope()->find($id);

        switch ($checkPermission) {
            case true:
                policy_authorize(ForumThreadPolicy::class, 'subscribe', $context, $thread);
                break;
            default:
                if (!policy_check(ForumThreadPolicy::class, 'subscribe', $context, $thread)) {
                    return;
                }
                break;
        }

        $repository = resolve(ForumThreadSubscribeRepositoryInterface::class);

        switch ($isSubscribed) {
            case true:
                $repository->subscribe($context, $id);
                break;
            default:
                $repository->unsubscribe($context, $id);
                break;
        }
    }

    public function updateThread(User $context, int $id, array $attributes): ForumThread
    {
        $thread = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(ForumThreadPolicy::class, 'update', $context, $thread);

        $hasForumNotification = $hasTitleNotification = $hasDescriptionNotification = false;

        $currentTitle = $thread->toTitle();

        $data = [
            'title'         => $attributes['title'],
            'is_closed'     => $attributes['is_closed'],
            'is_subscribed' => $attributes['is_subscribed'],
            'is_wiki'       => $attributes['is_wiki'],
            'tags'          => $attributes['tags'],
            'text'          => $attributes['text'],
            'forum_id'      => $attributes['forum_id'],
            'updated_at'    => $this->getModel()->freshTimestamp(),
        ];

        if ($data['forum_id'] > 0) {
            $hasForumNotification = $data['forum_id'] != $thread->forum_id;
        }

        if ($thread->toTitle() != $data['title']) {
            $hasTitleNotification = true;
        }

        if ($data['is_wiki']) {
            Arr::set($data, 'is_sticked', false);
        }

        if (null !== $thread->description) {
            $hasDescriptionNotification = $data['text'] != $thread->description->text_parsed;
        }

        if ($data['is_wiki'] == 0) {
            $data['forum_id'] = $attributes['forum_id'];
        }

        $hasSendWikiNotification = $context->entityId() != $thread->userId() && $data['is_wiki'] != $thread->is_wiki;

        $oldForumId = $thread->getForumId();

        $itemType = $attributes['item_type'];

        $itemId = $attributes['item_id'];

        $itemIntegration = $attributes['integrated_item'];

        $thread->fill($data);

        $thread->save();

        $thread->refresh();

        if (!$hasSendWikiNotification) {
            $this->handleNotificationWhenAdminUpdateThread($context, $thread);
        }

        if (Arr::has($data, 'forum_id') && $data['forum_id'] != $oldForumId) {
            $this->updateForumTotal($data['forum_id'], $oldForumId, $thread);
        }

        $this->subscribeThread($context, $thread->entityId(), $data['is_subscribed']);

        if (array_key_exists('attachments', $attributes) && null !== $attributes['attachments']) {
            ForumFacade::updateAttachments($thread, $attributes['attachments']);
        }

        $this->updateIntegratedItem($context, $thread, $itemType, $itemId, $itemIntegration);

        $thread->refresh();

        $actionType = $actionValue = null;

        $totalUpdateAction = 0;

        if ($hasDescriptionNotification) {
            $totalUpdateAction++;
        }

        if ($hasTitleNotification) {
            $totalUpdateAction++;
        }

        if ($hasForumNotification) {
            $totalUpdateAction++;
        }

        $hasUpdateInfoNotification = $totalUpdateAction > 1;

        switch ($hasUpdateInfoNotification) {
            case true:
                $actionType  = ForumSupport::UPDATE_INFO_ACTION;
                $actionValue = [
                    'current_title' => $currentTitle,
                ];
                break;
            default:
                if ($hasTitleNotification) {
                    $actionType  = ForumSupport::UPDATE_TITLE_ACTION;
                    $actionValue = [
                        'new_title'     => Arr::get($data, 'title'),
                        'current_title' => $currentTitle,
                    ];
                }

                if ($hasDescriptionNotification) {
                    $actionType  = ForumSupport::UPDATE_DESCRIPTION_ACTION;
                    $actionValue = [
                        'new_text'      => Arr::get($data, 'text'),
                        'current_title' => $currentTitle,
                    ];
                }

                if ($hasForumNotification) {
                    $newForum    = resolve(ForumRepositoryInterface::class)->find($data['forum_id']);
                    $actionType  = ForumSupport::MOVE_ACTION;
                    $actionValue = [
                        'current_title'       => $thread->toTitle(),
                        'current_forum_title' => $newForum->toTitle(),
                    ];
                }

                break;
        }

        if (!$hasSendWikiNotification && null !== $actionType) {
            $this->sendNotificationForThreadSubscription($context, $actionType, $thread, $actionValue);
        }

        if ($hasSendWikiNotification) {
            UpdateWiki::dispatch($context, $thread, $data['is_wiki']);
        }

        return $thread;
    }

    protected function handleNotificationWhenAdminUpdateThread(User $user, ForumThread $thread): void
    {
        if ($thread->isUser($user)) {
            return;
        }

        $notification       = new AdminUpdateThread($thread);
        $notificationParams = [$thread->user, $notification];

        Notification::send(...$notificationParams);
    }

    public function updateForumTotal(int $newForumId, int $oldForumId, ForumThread $thread): void
    {
        $repository = resolve(ForumRepositoryInterface::class);

        if ($newForumId > 0) {
            $repository->increaseTotal($newForumId, 'total_thread');
            $repository->increaseTotal($newForumId, 'total_comment', $thread->total_comment);
        }

        if ($oldForumId > 0) {
            $repository->decreaseTotal($oldForumId, 'total_thread');
            $repository->decreaseTotal($oldForumId, 'total_comment', $thread->total_comment);
        }
    }

    public function deleteThread(User $context, int $id): bool
    {
        $thread = $this
            ->withUserMorphTypeActiveScope()
            ->with(['posts', 'tagData', 'forum', 'lastReads'])
            ->find($id);

        policy_authorize(ForumThreadPolicy::class, 'delete', $context, $thread);

        $thread->delete();

        return true;
    }

    public function increaseTotalView(ForumThread $thread): void
    {
        $thread->incrementTotalView();
    }

    public function move(User $context, int $id, int $forumId): bool
    {
        $thread = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(ForumThreadPolicy::class, 'move', $context, $thread);

        $oldForumId = $thread->getForumId();

        if ($oldForumId == $forumId) {
            return true;
        }

        $thread->fill([
            'forum_id' => $forumId,
        ]);

        $thread->save();

        $this->updateForumTotal($forumId, $oldForumId, $thread);

        $newForum = resolve(ForumRepositoryInterface::class)->find($forumId);

        if (null !== $newForum) {
            $this->sendNotificationForThreadSubscription(
                $context,
                ForumSupport::MOVE_ACTION,
                $thread,
                ['current_title' => $thread->toTitle(), 'current_forum_title' => $newForum->toTitle()]
            );
        }

        return true;
    }

    public function stick(User $context, int $id, bool $isSticked): bool
    {
        $thread = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(ForumThreadPolicy::class, 'stick', $context, $thread);

        $thread->fill([
            'is_sticked' => $isSticked,
        ]);

        $thread->save();

        if ($isSticked) {
            $this->sendNotificationForThreadSubscription(
                $context,
                ForumSupport::STICK_ACTION,
                $thread,
                ['current_title' => $thread->toTitle()],
            );
        }

        return true;
    }

    public function close(User $context, int $id, bool $isClosed): bool
    {
        $thread = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(ForumThreadPolicy::class, 'close', $context, $thread);

        $hasNotification = $thread->is_closed != $isClosed;

        $thread->fill([
            'is_closed' => $isClosed,
        ]);

        $thread->save();

        if ($hasNotification) {
            switch ($isClosed) {
                case true:
                    $actionType = ForumSupport::CLOSE_ACTION;
                    break;
                default:
                    $actionType = ForumSupport::REOPEN_ACTION;
                    break;
            }

            $this->sendNotificationForThreadSubscription(
                $context,
                $actionType,
                $thread,
                ['current_title' => $thread->toTitle()]
            );
        }

        return true;
    }

    public function buildQueryForListing(User $context, User $owner, array $attributes): Builder
    {
        $sort      = $attributes['sort'];
        $sortType  = $attributes['sort_type'];
        $when      = $attributes['when'] ?? '';
        $view      = $attributes['view'] ?? '';
        $search    = $attributes['q'] ?? '';
        $searchTag = $attributes['tag'] ?? '';
        $forumId   = $attributes['forum_id'];
        $profileId = $attributes['user_id'];
        $contextId = $context->entityId();
        $ownerId   = null;

        if ($profileId) {
            $ownerId = $profileId;
        }

        $sortScope = new ThreadSortScope();
        $sortScope->setView($view)
            ->setSortType($sortType)
            ->setSort($sort);

        $whenScope = new WhenScope();

        $whenScope->setWhen($when);

        $viewScope = new ViewScope();

        $viewScope->setUser($context)
            ->setOwner($owner)
            ->setView($view)
            ->setProfileId($profileId);

        $privacyScope = new PrivacyScope();

        $privacyScope->setUserId($contextId);

        if (null !== $ownerId && $ownerId != $contextId) {
            $privacyScope->setOwnerId($ownerId);
        }

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query->leftJoin('forum_thread_text', function (JoinClause $joinClause) {
                $joinClause->on('forum_thread_text.id', '=', 'forum_threads.id');
            });

            $query = $query->addScope(new SearchScope(
                $search,
                ['forum_threads.title', 'forum_thread_text.text_parsed']
            ));
        }

        if ($searchTag != '') {
            $query = $query->addScope(new TagScope($searchTag));
        }

        $forceQueryForum = Arr::has($attributes, 'force_query_forum') || $forumId > 0;

        if ($forceQueryForum) {
            $forumScope = new ForumScope($forumId, ForumThread::ENTITY_TYPE);
            $query      = $query->addScope($forumScope);
        }

        if (Arr::has($attributes, 'exclude_thread_ids') && $attributes['exclude_thread_ids'] != '') {
            $excludeThreadIds = array_filter(explode(',', $attributes['exclude_thread_ids']), function ($value) {
                $value = trim($value);

                return is_numeric($value);
            });

            if (is_array($excludeThreadIds) && count($excludeThreadIds)) {
                $query->whereNotIn('forum_threads.id', $excludeThreadIds);
            }
        }

        return $query
            ->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($viewScope)
            ->addScope($privacyScope);
    }

    protected function applySetting(Builder $builder, User $owner, ?string $view): void
    {
        $viewable = [Browse::VIEW_MY, ThreadViewScope::VIEW_HISTORY, ThreadViewScope::VIEW_SUBSCRIBED];

        if (in_array($view, $viewable)) {
            return;
        }

        if ($owner instanceof HasPrivacyMember) {
            return;
        }

        $builder->where('forum_threads.owner_type', '=', $owner->entityType());
    }

    public function copy(User $context, array $attributes): ?ForumThread
    {
        $source = $this->withUserMorphTypeActiveScope()->find($attributes['thread_id']);

        $owner = $source->owner;

        $ownerCopy = $owner instanceof HasPrivacyMember ? $owner : $context;

        policy_authorize(ForumThreadPolicy::class, 'copy', $context, $owner, $source);

        $description = $source->description;

        $data = array_merge($source->toArray(), [
            'title'            => $attributes['title'],
            'forum_id'         => $attributes['forum_id'],
            'user_id'          => $context->entityId(),
            'user_type'        => $context->entityType(),
            'owner_id'         => $ownerCopy->entityId(),
            'owner_type'       => $ownerCopy->entityType(),
            'text'             => $description->text_parsed,
            'item_type'        => null,
            'item_id'          => 0,
            'is_closed'        => false,
            'total_comment'    => 0,
            'total_view'       => 0,
            'total_like'       => 0,
            'total_share'      => 0,
            'total_attachment' => 0,
            'is_sticked'       => false,
            'is_wiki'          => false,
            'created_at'       => now()->toDate(),
            'updated_at'       => now()->toDate(),
        ]);

        $target = new ForumThread();

        $target->fill($data);

        $success = $target->save();

        if (!$success) {
            return null;
        }

        $this->cloneAttachments($context, $target, $source);

        $this->cloneItem($context, $target, $source);

        $this->clonePosts($context, $target, $source);

        $this->cloneSubscribe($context, $target, $source);

        $target->refresh();

        return $target;
    }

    protected function cloneSubscribe(User $context, ForumThread $target, ForumThread $source): void
    {
        resolve(ForumThreadSubscribeRepositoryInterface::class)->subscribe($context, $target->entityId());
    }

    protected function cloneAttachments(User $context, ForumThread $dest, ForumThread $source): void
    {
        if ($source->total_attachment > 0) {
            $attachments = $source->attachments;
            if (null !== $attachments) {
                $total = 0;

                foreach ($attachments as $attachment) {
                    $attachment->clone($context, $dest->entityType(), $dest->entityId());
                    $total++;
                }

                if ($total > 0) {
                    $dest->update(['total_attachment' => $total]);
                }
            }
        }
    }

    protected function cloneItem(User $context, ForumThread $dest, ForumThread $source): void
    {
        if (null !== $source->item_type && null !== $source->item) {
            $response = app('events')->dispatch(
                'forum.thread.integrated_item.copy',
                [$context, $source->itemId(), $source->itemType()],
                true
            );
            if (is_array($response)) {
                $dest->update($response);
            }
        }
    }

    protected function clonePosts(User $context, ForumThread $dest, ForumThread $source): void
    {
        $posts = $source->posts()
            ->where('is_approved', 1)
            ->with(['postText', 'quotePost', 'quoteData'])
            ->get();

        $threadId = $dest->entityId();

        if (null !== $posts) {
            $postRepository   = resolve(ForumPostRepositoryInterface::class);
            $mappingResources = [];
            $clonedResources  = [];
            $clonedPosts      = [];

            foreach ($posts as $post) {
                $data = [
                    'thread_id'      => $threadId,
                    'force_approved' => true,
                    'text'           => $post->postText->text_parsed,
                    'is_cloned'      => true,
                ];

                $new = $postRepository->createPost($post->user, $post->owner, $data);

                if (null !== $new) {
                    Arr::set($mappingResources, $post->entityId(), $new);
                    Arr::set($clonedResources, $new->entityId(), $post);

                    $clonedPosts[] = $new;

                    $attachments = $post->attachments;

                    if (null !== $attachments) {
                        $total = 0;

                        foreach ($attachments as $attachment) {
                            $attachment->clone($context, $new->entityType(), $new->entityId());
                            $total++;
                        }

                        if ($total > 0) {
                            $new->fill([
                                'total_attachment' => $total,
                            ]);
                            $new->save();
                        }
                    }
                }
            }

            foreach ($clonedPosts as $clonedPost) {
                $oldPost = Arr::get($clonedResources, $clonedPost->entityId());

                if (null === $oldPost) {
                    continue;
                }

                $postRepository->cloneQuotePost($clonedPost, $oldPost, $mappingResources);
            }
        }
    }

    public function updateLastRead(User $context, int $threadId, int $postId): bool
    {
        $thread = $this->withUserMorphTypeActiveScope()->find($threadId);

        policy_authorize(ForumThreadPolicy::class, 'updateLastRead', $context, $thread);

        return resolve(ForumThreadLastReadRepositoryInterface::class)->updateLastRead($threadId, $postId, $context);
    }

    public function merge(User $context, array $attributes): array
    {
        $this->withUserMorphTypeActiveScope();

        $currentThread = $this
            ->with(['description'])
            ->find($attributes['current_thread_id']);

        $mergedThread = $this
            ->with(['description'])
            ->find($attributes['merged_thread_id']);

        policy_authorize(ForumThreadPolicy::class, 'merge', $context, $currentThread);

        policy_authorize(ForumThreadPolicy::class, 'merge', $context, $mergedThread);

        $condition = $attributes['current_thread_id'] > $attributes['merged_thread_id'];

        $newerThread = $condition ? $currentThread : $mergedThread;

        $olderThread = $condition ? $mergedThread : $currentThread;

        $this->mergeDescription($newerThread, $olderThread);

        $this->mergeTags($newerThread, $olderThread);

        $this->mergePosts($newerThread, $olderThread);

        $this->mergeView($newerThread, $olderThread);

        $this->mergeAttachments($newerThread, $olderThread);

        $this->mergePoll($newerThread, $olderThread);

        $newerThread->delete();

        $olderThread->refresh();

        $subscribers = resolve(ForumThreadSubscribeRepositoryInterface::class)->getSubscribersOfThreads([
            $olderThread->entityId(), $newerThread->entityId(),
        ]);

        if ($subscribers->count()) {
            $this->sendNotificationForThreadSubscription($context, 'merge', $olderThread, [
                'old_title' => $newerThread->toTitle(),
            ], $subscribers);
        }

        return [
            'new_item' => ResourceGate::asResource($olderThread, 'detail'),
            'old_id'   => $newerThread->entityId(),
        ];
    }

    protected function mergePoll(ForumThread $newerThread, ForumThread $olderThread): bool
    {
        if ($olderThread->item_type) {
            return true;
        }

        if (!$newerThread->item_type) {
            return true;
        }

        $olderThread->fill([
            'item_type' => $newerThread->item_type,
            'item_id'   => $newerThread->item_id,
        ]);

        $olderThread->save();

        $newerThread->fill([
            'item_type' => null,
            'item_id'   => 0,
        ]);

        $newerThread->save();

        return true;
    }

    protected function mergeView(ForumThread $newerThread, ForumThread $olderThread): bool
    {
        $mergedTotalView = 0;

        $newerTotalView = $newerThread->total_view;

        $olderTotalView = $olderThread->total_view;

        if ($newerTotalView > 0) {
            $mergedTotalView += $newerTotalView;
        }

        if ($olderTotalView > 0) {
            $mergedTotalView += $olderTotalView;
        }

        if ($mergedTotalView != $olderTotalView) {
            $olderThread->update(['total_view' => $mergedTotalView]);
        }

        return true;
    }

    protected function mergeAttachments(ForumThread $newerThread, ForumThread $olderThread): bool
    {
        $mergedTotalAttachment = 0;

        $newerTotalAttachment = $newerThread->total_attachment;

        $olderTotalAttachment = $olderThread->total_attachment;

        if ($newerTotalAttachment > 0) {
            $mergedTotalAttachment += $newerTotalAttachment;
        }

        if ($olderTotalAttachment > 0) {
            $mergedTotalAttachment += $olderTotalAttachment;
        }

        if ($mergedTotalAttachment != $olderTotalAttachment) {
            $olderThread->update(['total_attachment' => $mergedTotalAttachment]);
        }

        return $newerThread->attachments()->update(['item_id' => $olderThread->entityId()]);
    }

    protected function mergeDescription(ForumThread $newerThread, ForumThread $olderThread): bool
    {
        $mergedDescription = '';

        if (null !== $olderThread->description) {
            $mergedDescription .= $olderThread->description->text_parsed . "\n";
        }

        if (null !== $newerThread->description) {
            $mergedDescription .= $newerThread->description->text_parsed . "\n";

            $newerThread->description->delete();
        }

        $mergedDescription = parse_input()->prepare($mergedDescription);

        return $olderThread->description->update([
            'text_parsed' => $mergedDescription,
            'text'        => $mergedDescription,
        ]);
    }

    protected function mergeTags(ForumThread $newerThread, ForumThread $olderThread): bool
    {
        $olderTags = $olderThread->tags ?: [];
        $newerTags = $newerThread->tags ?: [];

        if (count(array_diff($olderTags, $newerTags)) == 0) {
            return true;
        }

        $mergedTags = array_unique(array_merge($olderTags, $newerTags));
        if (count(array_diff($mergedTags, $olderTags)) == 0) {
            return true;
        }

        $olderThread->update(['tags' => $mergedTags]);

        return true;
    }

    protected function mergePosts(ForumThread $newerThread, ForumThread $olderThread): bool
    {
        $mergedTotalPosts = 0;

        $totalOlderPosts = $olderThread->total_comment;

        $totalNewerPosts = $newerThread->total_comment;

        if ($totalNewerPosts > 0) {
            $mergedTotalPosts += $totalNewerPosts;
        }

        if ($totalOlderPosts > 0) {
            $mergedTotalPosts += $totalOlderPosts;
        }

        if ($mergedTotalPosts != $totalOlderPosts) {
            $olderThread->update(['total_comment' => $mergedTotalPosts]);

            if ($totalNewerPosts > 0 && null !== $olderThread->forum) {
                resolve(ForumRepositoryInterface::class)->increaseTotal(
                    $olderThread->forum->entityId(),
                    'total_comment',
                    $totalNewerPosts
                );
            }
        }

        $newerThread->posts()->update([
            'thread_id'  => $olderThread->entityId(),
            'owner_id'   => $olderThread->ownerId(),
            'owner_type' => $olderThread->ownerType(),
        ]);

        $this->updatePostId($olderThread);

        return true;
    }

    public function sendNotificationForThreadSubscription(
        User $context,
        string $actionType,
        ?ForumThread $thread,
        ?array $actionValue = null,
        ?Collection $subscribers = null
    ): void {
        SubscribedThread::dispatch($context, $actionType, $thread, $actionValue, $subscribers);
    }

    public function processAfterViewDetail(User $context, ForumThread $thread): ForumThread
    {
        $this->increaseTotalView($thread);

        $lastReadRepository = resolve(ForumThreadLastReadRepositoryInterface::class);

        $threadId = $thread->entityId();

        $hasRead = $lastReadRepository->hasRead($context, $threadId);

        switch ($hasRead) {
            case true:
                $lastReadRepository->updateLastView($context, $threadId);
                break;
            default:
                $lastReadRepository->updateLastRead($threadId, 0, $context);
                break;
        }

        $thread->refresh();

        return $thread;
    }

    public function updatePostId(ForumThread $thread): void
    {
        $update = [
            'first_post_id' => 0,
            'last_post_id'  => 0,
        ];

        $thread->load(['firstPost', 'lastPost']);

        if ($thread->firstPost instanceof ForumPost) {
            Arr::set($update, 'first_post_id', $thread->firstPost->entityId());
        }

        if ($thread->lastPost instanceof ForumPost) {
            Arr::set($update, 'last_post_id', $thread->lastPost->entityId());
        }

        $thread->updateQuietly($update);
    }
}
