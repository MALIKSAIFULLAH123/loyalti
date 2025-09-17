<?php

namespace MetaFox\Video\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Video\Http\Requests\v1\Video\Admin\BatchUpdateRequest;
use MetaFox\Video\Http\Requests\v1\Video\Admin\IndexRequest;
use MetaFox\Video\Http\Resources\v1\Video\Admin\VideoItem;
use MetaFox\Video\Http\Resources\v1\Video\Admin\VideoItemCollection as ItemCollection;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Policies\VideoPolicy;
use MetaFox\Video\Repositories\VerifyProcessRepositoryInterface;
use MetaFox\Video\Repositories\VideoAdminRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Video\Http\Controllers\Api\VideoAdminController::$controllers;
 */

/**
 * Class VideoAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @group video
 * @authenticated
 * @admincp
 */
class VideoAdminController extends ApiController
{
    public function __construct(protected VideoAdminRepositoryInterface $repository, protected VerifyProcessRepositoryInterface $verifyProcessRepository) {}

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->all();
        $context = user();
        $limit   = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        policy_authorize(VideoPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewVideos($context, $params)->paginate($limit, ['videos.*']);

        return new ItemCollection($data);
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();

        $this->repository->deleteVideo($context, $id);

        return $this->success([], [], __p('video::phrase.video_deleted_successfully'));
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
        $video = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$video->is_sponsor;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_successfully'
                : 'core::phrase.resource_unsponsored_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('video::phrase.video')]));
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
        $video = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$video->sponsor_in_feed;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('video::phrase.video')]));
    }

    /**
     * @param BatchUpdateRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
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

        return $this->success([], [], __p('video::phrase.video_s_has_been_approved'));
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

        if (!$context->hasPermissionTo('video.moderate')) {
            throw new AuthorizationException();
        }

        foreach ($ids as $id) {
            $this->repository->deleteVideo($context, $id);
        }

        return $this->success([], [], __p('video::phrase.video_s_deleted_successfully'));
    }

    public function verifyExistence(int $id): JsonResponse
    {
        $video = $this->repository->find($id);

        if ($video) {
            $this->repository->checkVideoExistence(user(), $video);
        }

        return $this->success(new VideoItem($video), [], __p('video::phrase.video_has_been_checked'));
    }

    /**
     *
     * @param BatchUpdateRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function batchVerifyExistence(BatchUpdateRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $ids     = Arr::get($params, 'id', []);
        $context = user();
        $ids     = array_values(Arr::sort($ids));

        $this->verifyProcessRepository->createProcess($context, [
            'video_ids'    => $ids,
            'total_videos' => count($ids),
        ]);

        return $this->success([], [], __p('video::phrase.mass_verification_video_existence'));
    }


    public function massVerifyExistence(): JsonResponse
    {
        $totalVideo = Video::query()->count();
        $context    = user();

        if ($this->verifyProcessRepository->checkProcessExist()) {
            abort(403, __p('video::phrase.mass_verification_video_existence_already_running'));
        }

        $this->verifyProcessRepository->createProcess($context, [
            'total_videos' => $totalVideo,
        ]);

        return $this->success([], [], __p('video::phrase.mass_verification_video_existence'));
    }
}
