<?php

namespace MetaFox\Poll\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Poll\Http\Requests\v1\Poll\Admin\BatchUpdateRequest;
use MetaFox\Poll\Http\Requests\v1\Poll\Admin\IndexRequest;
use MetaFox\Poll\Http\Resources\v1\Poll\Admin\PollItemCollection as ItemCollection;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Policies\PollPolicy;
use MetaFox\Poll\Repositories\PollAdminRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Poll\Http\Controllers\Api\PollAdminController::$controllers;
 */

/**
 * Class PollAdminController.
 */
class PollAdminController extends ApiController
{
    /**
     * @param PollAdminRepositoryInterface $repository
     */
    public function __construct(protected PollAdminRepositoryInterface $repository) {}

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     * @return ItemCollection
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $context = user();
        $limit   = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        policy_authorize(PollPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewPolls($context, $params)
            ->paginate($limit, ['polls.*']);

        return new ItemCollection($data);
    }

    /**
     * @param SponsorRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function sponsor(SponsorRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $sponsor = $params['sponsor'];
        $this->repository->sponsor(user(), $id, $sponsor);

        $poll             = $this->repository->find($id);
        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$poll->is_sponsor;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_successfully'
                : 'core::phrase.resource_unsponsored_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('poll::phrase.poll')]));
    }

    /**
     * @param SponsorInFeedRequest $request
     * @param int                  $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function sponsorInFeed(SponsorInFeedRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $sponsor = $params['sponsor'];
        $this->repository->sponsorInFeed(user(), $id, $sponsor);

        $poll             = $this->repository->find($id);
        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$poll->sponsor_in_feed;
        $message          = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('poll::phrase.poll')]));
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

        return $this->success([], [], __p('poll::phrase.poll_s_has_been_approved'));
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

        if (!user()->hasPermissionTo('poll.moderate')) {
            throw new AuthorizationException();
        }

        $query = $this->repository->getModel()->newQuery();

        $query->whereIn('id', $ids)
            ->get()
            ->each(function (Poll $model) {
                $model->delete();
            });

        return $this->success([], [], __p('poll::phrase.poll_s_deleted_successfully'));
    }
}
