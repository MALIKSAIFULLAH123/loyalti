<?php

namespace MetaFox\Music\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Music\Http\Requests\v1\Playlist\Admin\BatchUpdateRequest;
use MetaFox\Music\Http\Requests\v1\Playlist\Admin\IndexRequest;
use MetaFox\Music\Http\Resources\v1\Playlist\Admin\PlaylistItemCollection as ItemCollection;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Policies\PlaylistPolicy;
use MetaFox\Music\Repositories\PlaylistAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Music\Http\Controllers\Api\PlaylistAdminController::$controllers;
 */

/**
 * Class PlaylistAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class PlaylistAdminController extends ApiController
{
    /**
     * PlaylistAdminController Constructor
     *
     * @param PlaylistAdminRepositoryInterface $repository
     */
    public function __construct(protected PlaylistAdminRepositoryInterface $repository) {}

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

        policy_authorize(PlaylistPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewPlaylists($context, $params)->paginate($limit, ['music_playlists.*']);

        return new ItemCollection($data);
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

        if (!user()->hasPermissionTo('music_playlist.moderate')) {
            throw new AuthorizationException();
        }

        $query = $this->repository->getModel()->newQuery();

        $query->whereIn('id', $ids)
            ->get()
            ->each(function (Playlist $model) {
                $model->delete();
            });

        return $this->success([], [], __p('music::phrase.playlist_s_deleted_successfully'));
    }
}
