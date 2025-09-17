<?php

namespace MetaFox\Activity\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Activity\Http\Controllers\Api\HiddenController::$controllers;
 */

/**
 * Class HiddenController.
 * @group feed
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 */
class HiddenController extends ApiController
{
    /**
     * @var FeedRepositoryInterface
     */
    public FeedRepositoryInterface $repository;

    /**
     * @param FeedRepositoryInterface $repository
     */
    public function __construct(FeedRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Hide a feed.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function hideFeed(int $id): JsonResponse
    {
        $user = user();

        $feed = $this->repository->find($id);

        $this->repository->hideFeed($user, $feed);

        return $this->success([]); // @todo what will response when hide a feed ??
    }

    /**
     * Un-Hide a feed.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function unHideFeed(int $id): JsonResponse
    {
        $user = user();

        $feed = $this->repository->find($id);

        $this->repository->unHideFeed($user, $feed);

        return $this->success([]); // @todo what will response when un-hide a feed ??
    }
}
