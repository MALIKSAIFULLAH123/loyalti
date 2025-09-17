<?php

namespace MetaFox\Photo\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Photo\Http\Requests\v1\Album\Admin\BatchUpdateRequest;
use MetaFox\Photo\Http\Requests\v1\Album\Admin\IndexRequest;
use MetaFox\Photo\Http\Resources\v1\Album\Admin\AlbumItemCollection as ItemCollection;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Policies\AlbumPolicy;
use MetaFox\Photo\Repositories\AlbumAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Photo\Http\Controllers\Api\AlbumAdminController::$controllers;.
 */

/**
 * Class AlbumAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class AlbumAdminController extends ApiController
{
    /**
     * AlbumAdminController Constructor.
     *
     * @param AlbumAdminRepositoryInterface $repository
     */
    public function __construct(protected AlbumAdminRepositoryInterface $repository)
    {
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest           $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $limit   = Arr::get($params, 'limit', 100);
        $context = user();

        policy_authorize(AlbumPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewAlbums($context, $params)->paginate($limit, ['photo_albums.*']);

        return new ItemCollection($data);
    }

    /**
     * @param SponsorRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
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

        return $this->success([], [], __p($message, ['resource_name' => __p('photo::phrase.photo_album')]));
    }

    /**
     * @param  BatchUpdateRequest      $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function batchDelete(BatchUpdateRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $ids     = Arr::get($params, 'id', []);
        $context = user();

        $albums = $this->repository->findWhereIn('id', $ids);

        /**
         * @var Album $album
         */
        foreach ($albums as $album) {
            if (!policy_check(AlbumPolicy::class, 'delete', $context, $album)) {
                continue;
            }

            $this->repository->deleteAlbum($context, $album->entityId());
        }

        return $this->success([], [], __p('photo::phrase.photo_album_s_deleted_successfully'));
    }
}
