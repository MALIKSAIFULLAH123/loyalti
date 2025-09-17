<?php

namespace MetaFox\Forum\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Forum\Http\Requests\v1\ForumThread\Admin\BatchUpdateRequest;
use MetaFox\Forum\Http\Requests\v1\ForumThread\Admin\IndexRequest;
use MetaFox\Forum\Http\Resources\v1\ForumThread\Admin\ForumThreadItemCollection as ItemCollection;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Forum\Repositories\ForumThreadAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Forum\Http\Controllers\Api\ForumThreadAdminController::$controllers;
 */

/**
 * Class ForumThreadAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class ForumThreadAdminController extends ApiController
{
    /**
     * ForumThreadAdminController Constructor
     *
     * @param ForumThreadAdminRepositoryInterface $repository
     */
    public function __construct(protected ForumThreadAdminRepositoryInterface $repository) {}

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $limit   = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $context = user();

        policy_authorize(ForumThreadPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewThreads($context, $params)
            ->paginate($limit, ['forum_threads.*']);

        return new ItemCollection($data);
    }

    /**
     * Delete item
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $thread  = $this->repository->find($id);
        $context = user();

        policy_authorize(ForumThreadPolicy::class, 'delete', $context, $thread);

        $thread->delete();

        return $this->success([], [], __p('forum::phrase.thread_deleted_successfully'));
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

        return $this->success([], [], __p($message, ['resource_name' => __p('forum::phrase.thread')]));
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

        return $this->success([], [], __p($message, ['resource_name' => __p('forum::phrase.thread')]));
    }

    /**
     * @param BatchUpdateRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function batchApprove(BatchUpdateRequest $request): JsonResponse
    {
        $params = $request->validated();
        $ids    = Arr::get($params, 'id', []);

        foreach ($ids as $id) {
            $model = $this->repository->find($id);

            if ($model->isApproved()) {
                continue;
            }

            $this->repository->approve(user(), $id);
        }

        return $this->success([], [], __p('forum::phrase.thread_s_successfully_approved'));
    }

    /**
     *
     * @param BatchUpdateRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function batchDelete(BatchUpdateRequest $request): JsonResponse
    {
        $params = $request->validated();
        $ids    = Arr::get($params, 'id', []);

        if (!user()->hasPermissionTo('forum_thread.moderate')) {
            throw new AuthorizationException();
        }

        $query = $this->repository->getModel()->newQuery();

        $query->whereIn('id', $ids)
            ->get()
            ->each(function (ForumThread $model) {
                $model->delete();
            });

        return $this->success([], [], __p('forum::phrase.thread_s_deleted_successfully'));
    }
}
