<?php

namespace MetaFox\Video\Http\Controllers\Api\v1;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\Video\Contracts\ProviderManagerInterface;
use MetaFox\Video\Http\Requests\v1\Video\FetchRequest;
use MetaFox\Video\Http\Requests\v1\Video\IndexRequest;
use MetaFox\Video\Http\Requests\v1\Video\StoreRequest;
use MetaFox\Video\Http\Requests\v1\Video\UpdateRequest;
use MetaFox\Video\Http\Resources\v1\Video\VideoDetail;
use MetaFox\Video\Http\Resources\v1\Video\VideoItemCollection;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Policies\VideoPolicy;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use MetaFox\Video\Support\Facade\Video as FacadeVideo;

/**
 * Class CategoryController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 *
 * @ignore
 * @codeCoverageIgnore
 * @group video
 * @authenticated
 */
class VideoController extends ApiController
{
    public VideoRepositoryInterface $repository;

    public function __construct(VideoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
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

            policy_authorize(VideoPolicy::class, 'viewOnProfilePage', $context, $owner);
        }

        policy_authorize(VideoPolicy::class, 'viewAny', $context, $owner);

        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->getRandomSponsoredItems($context, $limit ?? 4),
            default              => $this->repository->viewVideos($context, $owner, $params),
        };

        return $this->success(new VideoItemCollection($data));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function show(int $id): JsonResponse
    {
        $context = user();
        $video   = $this->repository->viewVideo($context, $id);

        if (null == $video) {
            return $this->error(
                __p('core::phrase.the_entity_name_you_are_looking_for_can_not_be_found', ['entity_name' => 'video']),
                403
            );
        }

        return $this->success(new VideoDetail($video), [], '');
    }

    /**
     * Create a resource.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws Exception
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = $owner = user();
        $params  = $request->validated();

        app('flood')->checkFloodControlWhenCreateItem(user(), Video::ENTITY_TYPE);

        if ($params['owner_id'] > 0) {
            if ($context->entityId() != $params['owner_id']) {
                $owner = UserEntity::getById($params['owner_id'])->detail;
            }
        }

        $video = $this->repository->createVideo($context, $owner, $params);

        if (!$video->isApproved()) {
            return $this->success(new VideoDetail($video), [], __p('core::phrase.thanks_for_your_item_for_approval'));
        }

        if ($video->in_process) {
            return $this->info(new VideoDetail($video), [], __p('video::phrase.video_in_process_message'));
        }

        $pendingMessage = $video->getOwnerPendingMessage();
        $message        = $pendingMessage ?? __p('video::phrase.video_was_uploaded_successfully');

        $resource = new VideoDetail($video);
        if (!Arr::get($params, 'is_posted_from_feed')) {
            $meta = $this->repository->askingForPurchasingSponsorship($context, $video);

            return $this->success($resource, $meta, $message);
        }

        $feed     = $video->activity_feed;
        $resource = $feed ? ResourceGate::asDetail($feed) : ['id' => 0];

        return $this->success($resource, [], $message);
    }

    /**
     * Update a resource.
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
        $context = user();
        $params  = $request->validated();
        $video   = $this->repository->find($id);

        if (Arr::has($params, 'privacy') && !$context->can('updatePrivacy', [$video, $params['privacy']])) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_is_either_sponsored_or_featured'));
        }

        $data = $this->repository->updateVideo($context, $id, $params);

        return $this->success(new VideoDetail($data), [], __p('video::phrase.video_updated_successfully'));
    }

    /**
     * Delete a resource.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $context  = user();
        $resource = $this->repository->find($id);

        $collection       = $resource->group;
        $collectionFeed   = $collection instanceof Content ? $collection->activity_feed : null;
        $collectionFeedId = 0;

        if ($collectionFeed instanceof Content) {
            $collectionFeedId = $collectionFeed->entityId();
        }
        
        policy_authorize(VideoPolicy::class, 'delete', $context, $resource);

        $this->repository->deleteVideo($context, $id);

        return $this->success([
            'id'      => $id,
            'feed_id' => $collectionFeedId,
        ], [], __p('video::phrase.video_deleted_successfully'));
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

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');
        $message = __p($message, ['resource_name' => __p('video::phrase.video')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new VideoDetail($video), [], $message);
    }

    /**
     * @param FeatureRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function feature(FeatureRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $feature = (int) $params['feature'];
        $context = user();

        match ($feature) {
            1       => $this->repository->featureFree($context, $id),
            default => $this->repository->unfeature($context, $id),
        };

        $message = match ($feature) {
            1       => __p('video::phrase.video_featured_successfully'),
            default => __p('video::phrase.video_unfeatured_successfully'),
        };

        $video = $this->repository->find($id);

        return $this->success(new VideoDetail($video), [], $message);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function approve(int $id): JsonResponse
    {
        $video = $this->repository->approve(user(), $id);

        // @todo recheck response.
        return $this->success(new VideoDetail($video), [], __p('video::phrase.video_has_been_approved'));
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
        $message = __p($message, ['resource_name' => __p('video::phrase.video')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new VideoDetail($video), [], $message);
    }

    public function callback(string $provider, Request $request): JsonResponse
    {
        $response = [];

        try {
            $service = resolve(ProviderManagerInterface::class)->getVideoServiceClassByDriver($provider);
            if ($service instanceof VideoServiceInterface) {
                $response = $service->handleWebhook($request);
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }

        return $this->success([
            'success' => $response,
        ], [], '');
    }

    /**
     * View link.
     *
     * @param FetchRequest $request
     *
     * @return JsonResponse
     * @group core
     */
    public function fetch(FetchRequest $request): JsonResponse
    {
        $params = $request->validated();

        $url = $params['link'];

        $data = FacadeVideo::parseLink($url);

        $isVideo = Arr::get($data, 'is_video') ?? false;

        if (!$isVideo) {
            return $this->error(__p('video::phrase.unsupported_link_with_providers'));
        }

        return $this->success($data);
    }

    public function increaseView(int $id): JsonResponse
    {
        $video = $this->repository->increaseView($id);

        return $this->success(new VideoDetail($video), [], '');
    }
}
