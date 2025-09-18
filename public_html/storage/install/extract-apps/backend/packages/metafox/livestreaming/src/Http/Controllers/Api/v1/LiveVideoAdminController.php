<?php

namespace MetaFox\LiveStreaming\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\Admin\BatchUpdateRequest;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\Admin\IndexRequest;
use MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo\Admin\LiveVideoItemCollection as ItemCollection;
use MetaFox\LiveStreaming\Policies\LiveVideoPolicy;
use MetaFox\LiveStreaming\Repositories\LiveVideoAdminRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
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
 * | @link \MetaFox\LiveStreaming\Http\Controllers\Api\LiveVideoAdminController::$controllers;
 */

/**
 * Class LiveVideoAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class LiveVideoAdminController extends ApiController
{

    /**
     * LiveVideoAdminController Constructor
     *
     * @param LiveVideoAdminRepositoryInterface $repository
     * @param LiveVideoRepositoryInterface      $repositoryLiveVideo
     */
    public function __construct(
        protected LiveVideoAdminRepositoryInterface $repository,
        protected LiveVideoRepositoryInterface      $repositoryLiveVideo,
    ) {}

    /**
     * @param IndexRequest $request
     * @return ItemCollection
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $context = user();
        $owner   = $context;
        $limit   = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        policy_authorize(LiveVideoPolicy::class, 'viewAny', $context, $owner);

        $data = $this->repository->viewLiveVideos($context, $params)
            ->paginate($limit, ['livestreaming_live_videos.*']);

        return new ItemCollection($data);
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $context   = user();
        $liveVideo = $this->repository->find($id);
        policy_authorize(LiveVideoPolicy::class, 'delete', $context, $liveVideo);

        $this->repositoryLiveVideo->stopLiveStream($id, $liveVideo, null, true);
        $this->repositoryLiveVideo->deleteLiveVideo($context, $id);

        $message = __p('livestreaming::phrase.live_video_was_deleted_successfully');

        if (ob_get_level() > 0) {
            ob_clean();
        }

        return $this->success([], [], $message);
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

        $song = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$song->is_sponsor;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_successfully'
                : 'core::phrase.resource_unsponsored_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('livestreaming::phrase.live_video')]));
    }

    /**
     * Sponsor live video in feed.
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
        $params  = $request->validated();
        $sponsor = $params['sponsor'];

        $this->repository->sponsorInFeed(user(), $id, $sponsor);

        $song = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$song->sponsor_in_feed;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('livestreaming::phrase.live_video')]));
    }

    /**
     * Approve live video.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function approve(int $id): JsonResponse
    {
        $this->repository->approve(user(), $id);

        return $this->success([], [], __p('livestreaming::phrase.live_video_has_been_approved'));
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

        return $this->success([], [], __p('livestreaming::phrase.live_video_s_has_been_approved'));
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
        $params  = $request->validated();
        $ids     = Arr::get($params, 'id', []);
        $context = user();

        if (!$context->hasPermissionTo('live_video.moderate')) {
            throw new AuthorizationException();
        }

        foreach ($ids as $id) {
            $liveVideo = $this->repository->find($id);
            $this->repositoryLiveVideo->stopLiveStream($id, $liveVideo, null, true);
            $this->repositoryLiveVideo->deleteLiveVideo($context, $id);
        }

        if (ob_get_level() > 0) {
            ob_clean();
        }

        return $this->success([], [], __p('livestreaming::phrase.live_video_s_deleted_successfully'));
    }
}
