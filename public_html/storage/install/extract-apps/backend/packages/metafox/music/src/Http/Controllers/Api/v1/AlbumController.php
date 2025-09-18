<?php

namespace MetaFox\Music\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Music\Http\Requests\v1\Album\IndexRequest;
use MetaFox\Music\Http\Requests\v1\Album\ItemsRequest;
use MetaFox\Music\Http\Requests\v1\Album\StoreRequest;
use MetaFox\Music\Http\Requests\v1\Album\UpdateRequest;
use MetaFox\Music\Http\Resources\v1\Album\AlbumDetail;
use MetaFox\Music\Http\Resources\v1\Album\AlbumItemCollection;
use MetaFox\Music\Http\Resources\v1\Song\SongPlayCollection;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Policies\AlbumPolicy;
use MetaFox\Music\Policies\SongPolicy;
use MetaFox\Music\Repositories\AlbumRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class AlbumController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AlbumController extends ApiController
{
    public AlbumRepositoryInterface $repository;

    public function __construct(AlbumRepositoryInterface $repository)
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

            policy_authorize(SongPolicy::class, 'viewOnProfilePage', $context, $owner);
        }

        policy_authorize(AlbumPolicy::class, 'viewAny', $context, $owner);

        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->getRandomSponsoredItems($context, $limit ?? 4),
            default              => $this->repository->viewAlbums($context, $owner, $params),
        };

        return $this->success(new AlbumItemCollection($data));
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $context = $owner = user();
        $params  = $request->validated();

        if ($params['owner_id'] > 0) {
            if ($context->entityId() != $params['owner_id']) {
                $owner = UserEntity::getById($params['owner_id'])->detail;
            }
        }

        $album = $this->repository->createAlbum($context, $owner, $params);

        $message = __p(
            'core::phrase.resource_create_success',
            ['resource_name' => __p('music::phrase.album')]
        );
        $meta    = $this->repository->askingForPurchasingSponsorship($context, $album);

        return $this->success([
            'id'            => $album->entityId(),
            'module_name'   => $album->moduleName(),
            'resource_name' => $album->entityType(),
        ], $meta, $message);
    }

    public function show(int $id): JsonResponse
    {
        $context = user();

        $album = $this->repository->viewAlbum($context, $id);

        if (null == $album) {
            return $this->error(
                __p('core::phrase.the_entity_name_you_are_looking_for_can_not_be_found', ['entity_name' => 'album']),
                403
            );
        }

        return $this->success(new AlbumDetail($album), [], '');
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
        $data    = $this->repository->updateAlbum($context, $id, $params);

        return $this->success(new AlbumDetail($data), [], __p('core::phrase.resource_update_success', ['resource_name' => __p('music::phrase.album')]));
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
        $context = user();

        $this->repository->deleteAlbum($context, $id);

        return $this->success([
            'id' => $id,
        ], [], __p('music::phrase.album_deleted_successfully'));
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

        /**
         * @var Album $album
         */
        $album = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$album->is_sponsor;

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');
        $message = __p($message, ['resource_name' => __p('core::web.music_album')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new AlbumDetail($album), [], $message);
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
            1       => __p('music::phrase.album_featured_successfully'),
            default => __p('music::phrase.album_unfeatured_successfully'),
        };

        $album = $this->repository->find($id);

        return $this->success(new AlbumDetail($album), [], $message);
    }

    /**
     * Display a listing of the resource.
     *
     * @param ItemsRequest $request
     * @param int          $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function items(ItemsRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $context = user();
        $album   = $this->repository->find($id);

        if (!policy_check(SongPolicy::class, 'viewAny', $context)) {
            return $this->success();
        }

        policy_authorize(AlbumPolicy::class, 'view', $context, $album);

        $data = $this->repository->viewAlbumItems($context, $id, $params);

        return $this->success(new SongPlayCollection($data));
    }

    /**
     * Sponsor music in feed.
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

        /**
         * @var Album $album
         */
        $album = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$album->sponsor_in_feed;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');
        $message = __p($message, ['resource_name' => __p('core::web.music_album')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new AlbumDetail($album), [], $message);
    }
}
