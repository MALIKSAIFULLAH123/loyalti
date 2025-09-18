<?php

namespace MetaFox\LiveStreaming\Http\Controllers\Api\v1;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\GetLiveByStreamKeyRequest;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\IndexRequest;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\StoreRequest;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\StreamKeyValidateRequest;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\UpdateRequest;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\UpdateViewerRequest;
use MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo\LiveVideoDetail;
use MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo\LiveVideoItemCollection;
use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Models\StreamingService;
use MetaFox\LiveStreaming\Policies\LiveVideoPolicy;
use MetaFox\LiveStreaming\Repositories\Eloquent\LiveVideoRepository;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\LiveStreaming\Support\ServiceManager;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\LiveStreaming\Http\Controllers\Api\LiveVideoController::$controllers;.
 */

/**
 * Class LiveVideoController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class LiveVideoController extends ApiController
{
    /**
     * @var LiveVideoRepositoryInterface
     */
    private LiveVideoRepositoryInterface $repository;

    /**
     * LiveVideoController Constructor.
     *
     * @param LiveVideoRepositoryInterface $repository
     */
    public function __construct(LiveVideoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return mixed
     * @throws AuthenticationException|AuthorizationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $owner   = $context;
        $view    = Arr::get($params, 'view');
        $limit   = Arr::get($params, 'limit');

        if ($params['user_id'] > 0) {
            $owner = UserEntity::getById($params['user_id'])->detail;

            policy_authorize(LiveVideoPolicy::class, 'viewOnProfilePage', $context, $owner);
        }

        policy_authorize(LiveVideoPolicy::class, 'viewAny', $context, $owner);

        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->getRandomSponsoredItems($context, $limit ?? 4),
            default              => $this->repository->viewLiveVideos($context, $owner, $params),
        };

        return $this->success(new LiveVideoItemCollection($data));
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = $owner = user();
        $params  = $request->validated();

        if (isset($params['owner_id']) && $params['owner_id'] > 0) {
            if ($context->entityId() != $params['owner_id']) {
                $owner = UserEntity::getById($params['owner_id'])->detail;
            }
        }

        policy_authorize(LiveVideoPolicy::class, 'create', $context, $owner);

        $data = $this->repository->createLiveVideo($context, $owner, $params);
        $meta = $this->repository->askingForPurchasingSponsorship($context, $data);

        return $this->success(new LiveVideoDetail($data), $meta, );
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return LiveVideoDetail
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function show(int $id): LiveVideoDetail
    {
        $context   = user();
        $liveVideo = $this->repository->find($id);
        policy_authorize(LiveVideoPolicy::class, 'view', $context, $liveVideo);

        $data = $this->repository->viewLiveVideo(user(), $id);

        return new LiveVideoDetail($data);
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params    = $request->validated();
        $status    = Arr::get($params, 'status');
        $context   = user();
        $liveVideo = $this->repository->find($id);

        $playbackIds      = Arr::get($params, 'playback_ids');
        $isMobilePlayback = is_array($playbackIds) && count($playbackIds) && MetaFox::isMobile();

        if ($status == LiveVideoRepository::STATUS_IDLE) {
            if ($liveVideo instanceof LiveVideo && $liveVideo->is_streaming) {
                policy_authorize(LiveVideoPolicy::class, 'manageLiveVideo', $context, $liveVideo);
                $data = $this->repository->stopLiveStream($id);
            } else {
                $data = $liveVideo;
            }
        } else {
            if (!$isMobilePlayback) {
                policy_authorize(LiveVideoPolicy::class, 'update', $context, $liveVideo);
            }
            $data = $this->repository->updateLiveVideo($context, $id, $params);
        }
        $response = new LiveVideoDetail($data);

        return $this->success($response, [], __p('livestreaming::phrase.live_video_was_updated_successfully'));
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

        $this->repository->stopLiveStream($id, $liveVideo, null, true);
        $this->repository->deleteLiveVideo($context, $id);

        $message = __p('livestreaming::phrase.live_video_was_deleted_successfully');

        if (ob_get_level() > 0) {
            ob_clean();
        }

        return $this->success([
            'id' => $id,
        ], [], $message);
    }

    public function callback(string $provider, Request $request): JsonResponse
    {
        $service = resolve(ServiceManager::class)->getStreamingServiceByDriver($provider);

        if (!$service instanceof StreamingService) {
            return $this->error(__p('livestreaming::phrase.no_active_streaming_service'));
        }

        $provider = resolve($service->service_class, ['moduleId' => 'livestreaming', 'handler' => LiveVideoRepositoryInterface::class]);
        if (!$provider instanceof VideoServiceInterface) {
            return $this->error('Something went wrong');
        }

        $response = $provider->handleWebhook($request);

        return $this->success([
            'success' => $response,
        ], [], '');
    }

    public function pingStreamingVideo(int $id): JsonResponse
    {
        $liveVideo = $this->repository->find($id);

        if (!$liveVideo->is_streaming) {
            return $this->error('Something went wrong', 200);
        }

        $this->repository->pingStreaming($id);

        return $this->success([
            'id' => $id,
        ]);
    }

    public function pingViewer(int $id): JsonResponse
    {
        $liveVideo = $this->repository->find($id);

        if (!$liveVideo->is_streaming) {
            return $this->error('Something went wrong', 200);
        }

        $this->repository->pingViewer($id);

        return $this->success([
            'id' => $id,
        ]);
    }

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function startGoLive(StoreRequest $request): LiveVideoDetail
    {
        $context = $owner = user();
        $params  = $request->validated();

        policy_authorize(LiveVideoPolicy::class, 'create', $context);

        if (isset($params['owner_id']) && $params['owner_id'] > 0) {
            if ($context->entityId() != $params['owner_id']) {
                $owner = UserEntity::getById($params['owner_id'])->detail;
            }
        }

        $data = $this->repository->startGoLive($context, $owner, $params);

        return new LiveVideoDetail($data);
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function endLive($id): JsonResponse
    {
        $context   = user();
        $liveVideo = $this->repository->find($id);
        policy_authorize(LiveVideoPolicy::class, 'manageLiveVideo', $context, $liveVideo);

        $this->repository->stopLiveStream($id, $liveVideo);

        if (ob_get_level() > 0) {
            ob_clean();
        }

        return $this->success([
            'id' => $id,
        ], [], __p('livestreaming::phrase.successfully_end_live'));
    }

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updateViewer(UpdateViewerRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $liveVideo = $this->repository->find($id);

        if (!$liveVideo->is_streaming) {
            return $this->error('Something went wrong');
        }

        $liveVideo = $this->repository->updateViewerCount($liveVideo, $params);

        if ($liveVideo) {
            return $this->success([
                'id' => $id,
            ]);
        }

        return $this->error('Something went wrong');
    }

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws Exception
     */
    public function removeViewer($id): JsonResponse
    {
        $liveVideo = $this->repository->find($id);

        if (!$liveVideo->is_streaming) {
            return $this->error('Something went wrong');
        }

        $result = $this->repository->removeViewerCount($liveVideo, user());

        if ($result) {
            return $this->success([
                'id' => $liveVideo->entityId(),
            ]);
        }

        return $this->error('Something went wrong');
    }

    /**
     * Sponsor live video.
     *
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

        $isSponsor = (bool) $sponsor;

        $message = $isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully';
        $message = __p($message, ['resource_name' => __p('livestreaming::phrase.live_video')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success([
            'id'         => $id,
            'is_sponsor' => $isSponsor,
        ], [], $message);
    }

    /**
     * Feature live video.
     *
     * @param FeatureRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function feature(FeatureRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $feature = (int) $params['feature'];

        $context = user();

        match ($feature) {
            1       => $this->repository->featureFree($context, $id),
            default => $this->repository->unfeature($context, $id),
        };

        $message = match ($feature) {
            1       => __p('livestreaming::phrase.live_video_featured_successfully'),
            default => __p('livestreaming::phrase.live_video_unfeatured_successfully'),
        };

        $item = $this->repository->find($id);

        return $this->success(new LiveVideoDetail($item), [], $message);
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
        $resource = $this->repository->approve(user(), $id);

        return $this->success(new LiveVideoDetail($resource), [], __p('livestreaming::phrase.live_video_has_been_approved'));
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

        $isSponsor        = (bool) $sponsor;
        $liveVideo        = $this->repository->find($id);
        $isPendingSponsor = $isSponsor && !$liveVideo->sponsor_in_feed;
        $message          = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        $message = __p($message, ['resource_name' => __p('livestreaming::phrase.live_video')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new LiveVideoDetail($liveVideo), [], $message);
    }

    /**
     * Validate stream key.
     * @param StreamKeyValidateRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function validateStreamKey(StreamKeyValidateRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $result = $this->repository->validateStreamKey($context, $params);

        return $this->success($result);
    }

    /**
     * @throws AuthenticationException
     */
    public function getLiveVideoByStreamKey(GetLiveByStreamKeyRequest $request): JsonResponse
    {
        $context   = user();

        $params = $request->validated();

        $liveVideoId = $this->repository->getLiveByStreamKey($context, $params);

        return $this->success([
            'id' => $liveVideoId,
        ]);
    }
}
