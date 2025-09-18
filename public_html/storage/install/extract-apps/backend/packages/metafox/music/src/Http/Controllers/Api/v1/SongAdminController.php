<?php

namespace MetaFox\Music\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Music\Http\Requests\v1\Song\Admin\BatchUpdateRequest;
use MetaFox\Music\Http\Requests\v1\Song\Admin\IndexRequest;
use MetaFox\Music\Http\Resources\v1\Song\Admin\SongItemCollection as ItemCollection;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Policies\SongPolicy;
use MetaFox\Music\Repositories\SongAdminRepositoryInterface;
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
 * | @link \MetaFox\Music\Http\Controllers\Api\SongAdminController::$controllers;
 */

/**
 * Class SongAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class SongAdminController extends ApiController
{
    /**
     * SongAdminController Constructor
     *
     * @param SongAdminRepositoryInterface $repository
     */
    public function __construct(protected SongAdminRepositoryInterface $repository) {}

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

        policy_authorize(SongPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewSongs($context, $params)->paginate($limit, ['music_songs.*']);

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

        $song = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$song->is_sponsor;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_successfully'
                : 'core::phrase.resource_unsponsored_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('core::web.music_song')]));
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

        return $this->success([], [], __p($message, ['resource_name' => __p('core::web.music_song')]));
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

        return $this->success([], [], __p('music::phrase.song_s_has_been_approved'));
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

        if (!user()->hasPermissionTo('music_song.moderate')) {
            throw new AuthorizationException();
        }

        $query = $this->repository->getModel()->newQuery();

        $query->whereIn('id', $ids)
            ->get()
            ->each(function (Song $model) {
                $model->delete();
            });

        return $this->success([], [], __p('music::phrase.song_s_deleted_successfully'));
    }
}
