<?php

namespace MetaFox\Music\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use MetaFox\Music\Http\Requests\v1\Song\IndexRequest;
use MetaFox\Music\Http\Requests\v1\Song\StoreRequest;
use MetaFox\Music\Http\Requests\v1\Song\UpdateRequest;
use MetaFox\Music\Http\Resources\v1\Album\AlbumDetail;
use MetaFox\Music\Http\Resources\v1\Playlist\PlaylistDetail;
use MetaFox\Music\Http\Resources\v1\Song\SongDetail;
use MetaFox\Music\Http\Resources\v1\Song\SongItemCollection;
use MetaFox\Music\Policies\SongPolicy;
use MetaFox\Music\Repositories\PlaylistRepositoryInterface;
use MetaFox\Music\Repositories\SongRepositoryInterface;
use MetaFox\Music\Support\Facades\Music;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Support\Facades\UserEntity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class SongController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SongController extends ApiController
{
    public SongRepositoryInterface $repository;

    public function __construct(SongRepositoryInterface $repository)
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

        policy_authorize(SongPolicy::class, 'viewAny', $context, $owner);

        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->getRandomSponsoredItems($context, $limit ?? 4),
            default              => $this->repository->viewSongs($context, $owner, $params),
        };

        return $this->success(new SongItemCollection($data));
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

        $song = $this->repository->createSong($context, $owner, $params);

        $pendingMessage = $song->getOwnerPendingMessage();

        $message = $pendingMessage ?? __p(
            'core::phrase.resource_create_success',
            ['resource_name' => __p('music::phrase.song')]
        );

        $meta = $this->repository->askingForPurchasingSponsorship($context, $song);

        return $this->success(new SongDetail($song), $meta, $message);
    }

    public function show(int $id): JsonResponse
    {
        $context = user();
        $song    = $this->repository->viewSong($context, $id);

        if (null == $song) {
            return $this->error(
                __p('core::phrase.the_entity_name_you_are_looking_for_can_not_be_found', ['entity_name' => 'song']),
                403
            );
        }

        return $this->success(new SongDetail($song), [], '');
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
        $data    = $this->repository->updateSong($context, $id, $params);

        return $this->success(new SongDetail($data), [], __p('core::phrase.resource_update_success', ['resource_name' => __p('music::phrase.song')]));
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
        $song    = $this->repository->find($id);

        $this->repository->deleteSong($context, $id);

        if ($song->album_id) {
            return $this->success(new AlbumDetail($song->album), [], __p('music::phrase.song_deleted_successfully'));
        }

        return $this->success([
            'id' => $id,
        ], [], __p('music::phrase.song_deleted_successfully'));
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

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');
        $message = __p($message, ['resource_name' => __p('core::web.music_song')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new SongDetail($song), [], $message);
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
            1       => __p('music::phrase.song_featured_successfully'),
            default => __p('music::phrase.song_unfeatured_successfully'),
        };

        $song = $this->repository->find($id);

        return $this->success(new SongDetail($song), [], $message);
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

        $song = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$song->sponsor_in_feed;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');
        $message = __p($message, ['resource_name' => __p('core::web.music_song')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new SongDetail($song), [], $message);
    }

    /**
     * Approve song.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function approve(int $id): JsonResponse
    {
        $song = $this->repository->approve(user(), $id);

        // @todo recheck response.
        return $this->success(new SongDetail($song), [], __p('music::phrase.song_has_been_approved'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function download(int $id): BinaryFileResponse
    {
        $context = user();
        $song    = $this->repository->downloadSong($context, $id);
        $ext     = pathinfo($song->download_url, PATHINFO_EXTENSION);
        $name    = sprintf('%s.%s', Str::slug($song->name), $ext);

        return response()
            ->download($song->download_url, $name)
            ->deleteFileAfterSend();
    }

    public function search(Request $request): JsonResponse
    {
        $entityType = $request->get('entity_type', Music::getDefaultSearchEntityType());

        $entityType = Music::convertEntityType($entityType);

        $prefix = trim(str_replace('music/search', '', $request->getPathInfo()), '/');

        $response = Route::dispatch(Request::create(sprintf('%s/%s', $prefix, $entityType), 'GET', $request->all()));

        if (!$response instanceof JsonResponse) {
            return $this->success([]);
        }

        return $response;
    }

    public function updateTotalPlay(int $id): JsonResponse
    {
        $song = $this->repository->find($id);

        $context = user();

        policy_authorize(SongPolicy::class, 'view', $context, $song);

        $this->repository->updateTotalPlay($song);

        return $this->success([]);
    }

    public function removeFromPlaylist(int $id, int $playlistId): JsonResponse
    {
        $context  = user();
        $song     = $this->repository->find($id);
        $playlist = resolve(PlaylistRepositoryInterface::class)->find($playlistId);

        policy_authorize(SongPolicy::class, 'removeFromPlaylist', $context, $song, $playlist);

        $this->repository->removeFromPlaylist($song, $playlist);

        return $this->success(new PlaylistDetail($playlist->refresh()), [], __p('music::phrase.song_successfully_removed_from_the_playlist'));
    }
}
