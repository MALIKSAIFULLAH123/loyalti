<?php

namespace MetaFox\Forum\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Forum\Jobs\CreatePost;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumPostQuote;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Policies\ForumPostPolicy;
use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Forum\Support\Browse\Scopes\ForumScope;
use MetaFox\Forum\Support\Browse\Scopes\PostViewScope;
use MetaFox\Forum\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Forum\Support\Facades\Forum;
use MetaFox\Forum\Support\Facades\ForumPost as ForumPostFacade;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Traits\UserMorphTrait;

class ForumPostRepository extends AbstractRepository implements ForumPostRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    public function model()
    {
        return ForumPost::class;
    }

    /**
     * @param User  $context
     * @param User  $owner
     * @param array $attributes
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewPosts(User $context, User $owner, array $attributes): Paginator
    {
        $threadId = Arr::get($attributes, 'thread_id');

        if ($threadId) {
            $thread = resolve(ForumThreadRepositoryInterface::class)->find($threadId);
            $owner = $thread->owner;
        }

        policy_authorize(ForumPostPolicy::class, 'viewAny', $context, $owner);

        $view = $attributes['view'];

        $postId = $attributes['post_id'];

        $page = 1;

        $limit = $attributes['limit'];

        $contextId = $context->entityId();

        $isContinueLastRead = $contextId > 0
            && $threadId
            && $view == PostViewScope::VIEW_CONTINUE_LAST_READ
            && !Arr::has($attributes, 'page');

        if (Arr::has($attributes, 'page')) {
            $page = $attributes['page'];
        }

        if ($view == Browse::VIEW_PENDING) {
            policy_authorize(ForumPostPolicy::class, 'approve', $context);
        }

        $relations = ForumPostFacade::getRelations();

        $query = $this->buildQueryForListing($context, $owner, $attributes);

        $this->applySetting($query, $owner, $view);

        $lastReadByPostId = null;

        $hasPost = $postId > 0;

        switch ($hasPost) {
            case true:
                $post = $this->getModel()->newModelQuery()
                    ->where('id', '=', $postId)
                    ->first();

                $lastReadByPostId = $post?->entityId();
                break;
            default:
                if ($isContinueLastRead) {
                    $lastRead = $thread->hasRead;
                    $lastReadByPostId = $lastRead?->post_id;
                }

                break;
        }

        if (null !== $lastReadByPostId) {
            $page = $this->getPageNumberForLastRead($query, $lastReadByPostId, $limit);
        }

        return $query
            ->with($relations)
            ->paginate($attributes['limit'], ['forum_posts.*'], 'page', $page);
    }

    public function viewPost(User $context, int $id): ForumPost
    {
        // TODO: Implement viewPost() method.
        return new ForumPost();
    }

    /**
     * @param User  $context
     * @param User  $owner
     * @param array $attributes
     * @return ForumPost
     * @throws AuthorizationException
     */
    public function createPost(User $context, User $owner, array $attributes): ForumPost
    {
        $thread = resolve(ForumThreadRepositoryInterface::class)->find($attributes['thread_id']);

        $isCloned = Arr::get($attributes, 'is_cloned', false);

        policy_authorize(ForumPostPolicy::class, 'reply', $context, $thread, $isCloned);

        $attributes = array_merge($attributes, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'owner_id'    => $thread->ownerId(),
            'owner_type'  => $thread->ownerType(),
            'is_approved' => (int)policy_check(ForumPostPolicy::class, 'autoApprove', $context, $owner),
        ]);

        $this->handlePendingMode($thread, $attributes);

        $post = new ForumPost($attributes);

        $post->save();

        if (!$post->isApproved()) {
            app('events')->dispatch('activity.feed.create_from_resource', [$post], true);
        }

        if (!$isCloned && array_key_exists('attachments', $attributes) && null !== $attributes['attachments']) {
            Forum::updateAttachments($post, $attributes['attachments']);
        }

        $post->refresh();

        if (!$isCloned && $attributes['is_approved']) {
            $this->sendNotificationForThreadSubscription($thread->entityId(), $post->entityId());
        }

        if ($attributes['is_approved']) {
            resolve(ForumThreadRepositoryInterface::class)->updatePostId($thread);
        }

        return $post;
    }

    protected function handlePendingMode(ForumThread $thread, array &$attributes): void
    {
        $owner = $thread->owner;
        if ($owner->hasPendingMode()) {
            $attributes['is_approved'] = 1;

            if ($owner->isPendingMode()) {
                $attributes['is_approved'] = 0;
            }
        }
    }

    public function updatePost(User $context, int $id, array $attributes): ForumPost
    {
        $post = $this->find($id);

        policy_authorize(ForumPostPolicy::class, 'update', $context, $post);

        $data = [
            'text' => $attributes['text'],
        ];

        $post->fill($data);

        $post->save();

        if (array_key_exists('attachments', $attributes) && null !== $attributes['attachments']) {
            Forum::updateAttachments($post, $attributes['attachments']);
        }

        $post->refresh();

        //TODO: send notification when update Thread with main post

        return $post;
    }

    public function deletePostInBackground(ForumPost $post): bool
    {
        $post->delete();

        if (!$post->thread instanceof ForumThread) {
            return true;
        }

        resolve(ForumThreadRepositoryInterface::class)->updatePostId($post->thread);

        return true;
    }

    public function deletePost(User $context, int $id): bool
    {
        $post = $this
            ->with(['thread', 'thread.firstPost', 'thread.lastPost'])
            ->find($id);

        policy_authorize(ForumPostPolicy::class, 'delete', $context, $post);

        return $this->deletePostInBackground($post);
    }

    public function quotePost(User $context, array $attributes): ?ForumPost
    {
        $quoteId = $attributes['quote_id'];

        unset($attributes['quote_id']);

        $quotePost = $this->find($quoteId);

        policy_authorize(ForumPostPolicy::class, 'quote', $context, $quotePost);

        Arr::set($attributes, 'thread_id', $quotePost->getThreadId());

        $post = $this->createPost($context, $quotePost->owner, $attributes);

        if (null !== $post) {
            $quoteModel = new ForumPostQuote();

            $quoteUser = $quotePost->user;

            $quoteModel->fill([
                'post_id'         => $post->entityId(),
                'quote_id'        => $quoteId,
                'quote_user_type' => $quoteUser->entityType(),
                'quote_user_id'   => $quoteUser->entityId(),
                'quote_content'   => $quotePost?->postText->text_parsed,
            ]);

            $quoteModel->save();

            $post->refresh();

            return $post;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function buildQueryForListing(User $context, User $owner, array $attributes): Builder
    {
        $sort = $attributes['sort'];
        $sortType = $attributes['sort_type'];
        $when = $attributes['when'] ?? '';
        $view = $attributes['view'] ?? '';
        $search = $attributes['q'] ?? '';
        $threadId = $attributes['thread_id'];
        $forumId = $attributes['forum_id'];
        $profileId = $attributes['user_id'];
        $contextId = $context->entityId();
        $ownerId = null;

        if ($profileId) {
            $ownerId = $profileId;
        }

        if ($contextId > 0 && $contextId == $profileId && $view != Browse::VIEW_PENDING) {
            $view = Browse::VIEW_MY;
        }

        if ($sort == Browse::SORT_MOST_DISCUSSED) {
            $sort = '';
        }
        $sortScope = new SortScope();
        $sortScope->setSort($sort)
            ->setSortType($sortType);

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $viewScope = new PostViewScope();
        $viewScope->setUser($context)
            ->setOwner($owner)
            ->setView($view)
            ->setProfileId($profileId);

        $privacyScope = new PrivacyScope();
        $privacyScope->setUserId($contextId);
        if (null !== $ownerId) {
            $privacyScope->setOwnerId($ownerId);
        }

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query = $query->join('forum_post_text', 'forum_post_text.id', '=', 'forum_posts.id')
                ->addScope(new SearchScope($search, ['text_parsed'], 'forum_post_text'));
        }

        if ($forumId > 0) {
            $forumScope = new ForumScope($forumId, ForumPost::ENTITY_TYPE);
            $query = $query->addScope($forumScope);
        }

        if ($threadId > 0) {
            $query = $query->where('forum_posts.thread_id', '=', $threadId);
        }

        return $query
            ->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($viewScope)
            ->addScope($privacyScope);
    }

    protected function applySetting(Builder $builder, User $owner, ?string $view): void
    {
        if (in_array($view, [Browse::VIEW_MY])) {
            return;
        }

        if ($owner instanceof HasPrivacyMember) {
            return;
        }

        $builder->where('forum_posts.owner_type', '=', $owner->entityType());
    }

    protected function getPageNumberForLastRead(Builder $builder, int $postId, int $limit = 20): int
    {
        $page = 1;

        if ($postId == 0) {
            return $page;
        }

        $clone = $builder->clone();

        $index = $clone->where('forum_posts.id', '<=', $postId)
            ->count();

        if ($index > $limit) {
            $page = $index / $limit;

            if ($index % $limit > 0) {
                $page += 1;
            }
        }

        return $page;
    }

    public function sendNotificationForThreadSubscription(int $threadId, int $postId): void
    {
        CreatePost::dispatch($threadId, $postId);
    }

    public function viewPosters(User $context, int $threadId, array $params = []): Collection
    {
        $thread = resolve(ForumThreadRepositoryInterface::class)->find($threadId);

        policy_authorize(ForumThreadPolicy::class, 'view', $context, $thread);

        $query = $this->getModel()->newModelQuery()
            ->where([
                'forum_posts.thread_id'   => $threadId,
                'forum_posts.is_approved' => 1,
            ]);

        $userId = $context->entityId();

        $resourceUserColumn = 'forum_posts.user_id';

        // Resources post by blocked users.
        $query->leftJoin(
            'user_blocked as blocked_owner',
            function (JoinClause $join) use ($resourceUserColumn, $userId) {
                $join->on('blocked_owner.owner_id', '=', $resourceUserColumn)
                    ->where('blocked_owner.user_id', '=', $userId);
            }
        )->whereNull('blocked_owner.owner_id');

        // Resources post by users blocked you.
        $query->leftJoin(
            'user_blocked as blocked_user',
            function (JoinClause $join) use ($resourceUserColumn, $userId) {
                $join->on('blocked_user.user_id', '=', $resourceUserColumn)
                    ->where('blocked_user.owner_id', '=', $userId);
            }
        )->whereNull('blocked_user.user_id');

        $userIds = $query->distinct('forum_posts.user_id')
            ->pluck('forum_posts.user_id')
            ->unique()
            ->toArray();

        if (!count($userIds)) {
            return collect([]);
        }

        return UserEntity::query()
            ->whereIn('id', $userIds)
            ->get();
    }

    public function cloneQuotePost(ForumPost $new, ForumPost $old, array $mappingResources = []): void
    {
        if (null === $old->quoteData) {
            return;
        }

        $quoteId = 0;

        if ($old->quoteData->quote_id && Arr::has($mappingResources, $old->quoteData->quote_id)) {
            $mapping = Arr::get($mappingResources, $old->quoteData->quote_id);

            if (null !== $mapping) {
                $quoteId = $mapping->entityId();
            }
        }

        $data = array_merge($old->quoteData->toArray(), [
            'post_id'  => $new->entityId(),
            'quote_id' => $quoteId,
        ]);

        $quote = new ForumPostQuote($data);

        $quote->save();
    }
}
