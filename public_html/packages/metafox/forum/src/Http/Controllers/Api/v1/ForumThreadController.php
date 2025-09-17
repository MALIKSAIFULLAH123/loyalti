<?php

namespace MetaFox\Forum\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Forum\Http\Requests\v1\ForumThread\CloseRequest;
use MetaFox\Forum\Http\Requests\v1\ForumThread\CopyRequest;
use MetaFox\Forum\Http\Requests\v1\ForumThread\IndexRequest;
use MetaFox\Forum\Http\Requests\v1\ForumThread\LastReadRequest;
use MetaFox\Forum\Http\Requests\v1\ForumThread\MergeRequest;
use MetaFox\Forum\Http\Requests\v1\ForumThread\MoveRequest;
use MetaFox\Forum\Http\Requests\v1\ForumThread\SearchSuggestionRequest;
use MetaFox\Forum\Http\Requests\v1\ForumThread\StickRequest;
use MetaFox\Forum\Http\Requests\v1\ForumThread\StoreRequest;
use MetaFox\Forum\Http\Requests\v1\ForumThread\SubscribeRequest;
use MetaFox\Forum\Http\Requests\v1\ForumThread\UpdateRequest;
use MetaFox\Forum\Http\Resources\v1\ForumThread\CopyForm;
use MetaFox\Forum\Http\Resources\v1\ForumThread\ForumThreadCollection as ItemCollection;
use MetaFox\Forum\Http\Resources\v1\ForumThread\ForumThreadDetail;
use MetaFox\Forum\Http\Resources\v1\ForumThread\ForumThreadDetail as Detail;
use MetaFox\Forum\Http\Resources\v1\ForumThread\MergeForm;
use MetaFox\Forum\Http\Resources\v1\ForumThread\MoveForm;
use MetaFox\Forum\Http\Resources\v1\ForumThread\SearchSuggestionCollection;
use MetaFox\Forum\Jobs\CopyThread;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class ForumThreadController.
 */
class ForumThreadController extends ApiController
{
    /**
     * @var ForumThreadRepositoryInterface
     */
    public ForumThreadRepositoryInterface $repository;

    public function __construct(ForumThreadRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();

        $context = user();

        $owner = $context;

        $forumId = Arr::get($params, 'forum_id');

        $view = Arr::get($params, 'view');

        $limit = Arr::get($params, 'limit');

        if ($params['user_id'] > 0) {
            $owner = UserEntity::getById($params['user_id'])->detail;

            if ($owner->isPendingMode()) {
                if (Arr::has($params, 'is_not_pending') && $params['is_not_pending'] != 1) {
                    gate_authorize($context, 'update', $owner, $owner);
                }
            }

            // If viewed on profile, but you don't have permission to view their profile, should see empty feed listing.
            if (!policy_check(ForumThreadPolicy::class, 'viewOnProfilePage', $context, $owner)) {
                throw new AuthorizationException();
            }
        }

        policy_authorize(ForumThreadPolicy::class, 'viewAny', $context, $owner);

        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->getRandomSponsoredItems($context, $limit ?? 4),
            default              => $this->repository->viewThreads($context, $owner, $params),
        };

        $collection = new ItemCollection($data);

        if ($forumId) {
            $forum = resolve(ForumRepositoryInterface::class)->find($forumId);

            $collection->setExtraMeta([
                'title'       => $forum->toTitle(),
                'description' => $forum->description,
            ]);
        }

        return $collection;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @throws AuthenticationException
     * @throws PermissionDeniedException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = $owner = user();

        $params = $request->validated();

        app('flood')->checkFloodControlWhenCreateItem($context, ForumThread::ENTITY_TYPE);

        $message = __p('quota::phrase.quota_control_invalid', ['entity_type' => __p('forum::phrase.forum_thread')]);

        app('quota')->checkQuotaControlWhenCreateItem($context, ForumThread::ENTITY_TYPE, 1, ['message' => $message]);

        if ($params['owner_id'] > 0) {
            if ($context->entityId() != $params['owner_id']) {
                $owner = UserEntity::getById($params['owner_id'])->detail;
            }
        }

        $thread = $this->repository->createThread($context, $owner, $params);

        $pendingMessage = $thread->getOwnerPendingMessage();

        $message = $pendingMessage ?? __p(
            'core::phrase.resource_create_success',
            ['resource_name' => __p('forum::phrase.thread')]
        );

        $meta = $thread->is_wiki ? [] : $this->repository->askingForPurchasingSponsorship($context, $thread);

        return $this->success(new Detail($thread), $meta, $message);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show($id)
    {
        $context = user();

        $data = $this->repository->viewThread($context, $id);

        if (null !== $data && $context->entityId() > 0) {
            $data = $this->repository->processAfterViewDetail($context, $data);
        }

        return new Detail($data);
    }

    /**
     * @param UpdateRequest       $request
     * @param                     $id
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, $id)
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->updateThread($context, $id, $params);

        return $this->success(new Detail($data), [], __p('forum::phrase.thread_edited_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $context = user();

        $this->repository->deleteThread($context, $id);

        return $this->success([
            'id' => $id,
        ], [], __p('forum::phrase.thread_deleted_successfully'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function approve(int $id): JsonResponse
    {
        $context = user();

        $thread = $this->repository->approve($context, $id);

        return $this->success(new ForumThreadDetail($thread), [], __p('forum::phrase.thread_successfully_approved'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function subscribe(SubscribeRequest $request, int $id): JsonResponse
    {
        $user = user();

        $params = $request->validated();

        $isSubscribed = $params['is_subscribed'];

        $this->repository->subscribeThread($user, $id, $isSubscribed, true);

        switch ($isSubscribed) {
            case false:
                $message = __p('forum::phrase.unsubscribed_thread_successfully');
                break;
            default:
                $message = __p('forum::phrase.subscribed_thread_successfully');
                break;
        }

        return $this->success([
            'id'            => $id,
            'is_subscribed' => $isSubscribed,
        ], [], $message);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getMoveForm(int $id): JsonResponse
    {
        $thread = $this->repository->find($id);

        $context = user();

        policy_authorize(ForumThreadPolicy::class, 'move', $context, $thread);

        $form = new MoveForm($thread);

        return $this->success($form);
    }

    /**
     * @param MoveRequest $request
     * @param int         $id
     *
     * @return JsonResponse
     */
    public function move(MoveRequest $request, int $id): JsonResponse
    {
        $context = user();

        $data = $request->validated();

        $this->repository->move($context, $id, $data['forum_id']);

        $thread = $this->repository->find($id);

        $resource = ResourceGate::asResource($thread, 'detail');

        return $this->success($resource, [], __p('forum::phrase.thread_moved_successfully'));
    }

    /**
     * @param StickRequest $request
     * @param int          $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function stick(StickRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $isSticked = $data['is_sticked'];

        $context = user();

        $this->repository->stick($context, $id, $isSticked);

        switch ($isSticked) {
            case true:
                $message = __p('forum::phrase.thread_successfully_sticked');
                break;
            default:
                $message = __p('forum::phrase.thread_successfully_unsticked');
                break;
        }

        return $this->success([
            'id'         => $id,
            'is_sticked' => $isSticked,
        ], [], $message);
    }

    /**
     * @param CloseRequest $request
     * @param int          $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function close(CloseRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $isClosed = $data['is_closed'];

        $context = user();

        $this->repository->close($context, $id, $isClosed);

        switch ($isClosed) {
            case true:
                $message = __p('forum::phrase.thread_closed_successfully');
                break;
            default:
                $message = __p('forum::phrase.thread_successfully_reopened');
                break;
        }

        return $this->success([
            'id'        => $id,
            'is_closed' => $isClosed,
        ], [], $message);
    }

    /**
     * @param SponsorRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function sponsor(SponsorRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $isSponsor = $params['is_sponsor'];

        $context = user();

        $this->repository->sponsor($context, $id, $isSponsor);

        $thread = $this->repository->find($id);

        $isPendingSponsor = $isSponsor && !$thread->is_sponsor;

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');
        $message = __p($message, ['resource_name' => __p('forum::phrase.thread')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new Detail($thread), [], $message);
    }

    /**
     * Sponsor thread in feed.
     *
     * @param SponsorInFeedRequest $request
     * @param int                  $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function sponsorInFeed(SponsorInFeedRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $isSponsor = (bool) $params['sponsor'];

        $this->repository->sponsorInFeed($context, $id, $isSponsor);

        $thread = $this->repository->find($id);

        $isPendingSponsor = $isSponsor && !$thread->sponsor_in_feed;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        $message = __p($message, ['resource_name' => __p('forum::phrase.thread')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new Detail($thread), [], $message);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getCopyForm(int $id): JsonResponse
    {
        $thread = $this->repository->find($id);

        $context = user();

        $owner = $thread->owner;

        policy_authorize(ForumThreadPolicy::class, 'copy', $context, $owner, $thread);

        $form = new CopyForm($thread);

        return $this->success($form);
    }

    /**
     * @param CopyRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function copy(CopyRequest $request): JsonResponse
    {
        $context = user();

        $data = $request->validated();

        CopyThread::dispatch($context, $data);

        return $this->success(
            [],
            [],
            __p('forum::phrase.your_thread_copying_progress_is_being_processed_please_waiting')
        );
    }

    /**
     * @param LastReadRequest $request
     * @param int             $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function updateLastRead(LastReadRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $postId = Arr::get($data, 'post_id');

        $context = user();

        if ($context->entityId() > 0) {
            $this->repository->updateLastRead($context, $id, $postId);
        }

        return $this->success();
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function getMergeForm(int $id): JsonResponse
    {
        $thread = $this->repository->find($id);

        $context = user();

        policy_authorize(ForumThreadPolicy::class, 'merge', $context, $thread);

        $form = new MergeForm($thread);

        return $this->success($form);
    }

    /**
     * @param MergeRequest $request
     *
     * @return JsonResponse
     */
    public function merge(MergeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $response = $this->repository->merge($context, $data);

        return $this->success($response, [], __p('forum::phrase.merged_successfully'));
    }

    /**
     * @param SearchSuggestionRequest $request
     *
     * @return JsonResponse
     */
    public function searchSuggestion(SearchSuggestionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $owner = $context;

        policy_authorize(ForumThreadPolicy::class, 'viewAny', $context, $owner);

        $items = $this->repository->viewThreads($context, $owner, $data);

        $collection = new SearchSuggestionCollection($items);

        return $this->success($collection);
    }
}
