<?php

namespace MetaFox\Activity\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Activity\Policies\FeedPolicy;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Activity\Repositories\PinRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity as Users;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Activity\Http\Controllers\Api\HiddenController::$controllers;
 */

/**
 * Class PinController.
 * @ignore
 * @codeCoverageIgnore
 * @group feed
 * @authenticated
 */
class PinController extends ApiController
{
    /**
     * @var PinRepositoryInterface
     */
    protected PinRepositoryInterface $repository;

    /**
     * @var FeedRepositoryInterface
     */
    protected FeedRepositoryInterface $feedRepository;

    public function __construct(PinRepositoryInterface $repository, FeedRepositoryInterface $feedRepository)
    {
        $this->repository     = $repository;
        $this->feedRepository = $feedRepository;
    }

    /**
     * Pin a feed.
     *
     * POST: feed/pin/{id}
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function pin(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'owner_id' => ['required', 'int'],
        ]);

        $user = user();

        $owner = Users::getById($data['owner_id'])->detail;

        $feed = $this->feedRepository->find($id);

        policy_authorize(FeedPolicy::class, 'pinItem', $user, $feed);

        $this->repository->pin($user, $owner, $id);

        $pins = $this->repository->getPinOwnerIds($user, $id);

        return $this->success($pins);
    }

    /**
     * Unpin a feed.
     *
     * DELETE: feed/unpin/{id}
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function unpin(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'owner_id' => ['required', 'int'],
        ]);

        $user = user();

        $owner = Users::getById($data['owner_id'])->detail;

        $feed = $this->feedRepository->find($id);

        policy_authorize(FeedPolicy::class, 'pinItem', $user, $feed);

        $this->repository->unpin($user, $owner, $id);

        $pins = $this->repository->getPinOwnerIds($user, $id);

        return $this->success($pins);
    }

    /**
     * Pin a feed.
     *
     * POST: feed/pin/{id}/home
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function pinHome(int $id): JsonResponse
    {
        $user = user();

        $feed = $this->feedRepository->find($id);

        policy_authorize(FeedPolicy::class, 'pinHome', $user, $feed);

        $this->repository->pinHome($user, $id);

        $pins = $this->repository->getPinOwnerIds($user, $id);

        return $this->success($pins);
    }

    /**
     * Unpin a feed.
     *
     * DELETE: feed/pin/{id}/home
     *
     * @param int $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function unpinHome(int $id): JsonResponse
    {
        $user = user();

        $this->repository->unpinHome($user, $id);

        $feed = $this->feedRepository->find($id);

        policy_authorize(FeedPolicy::class, 'pinHome', $user, $feed);

        $pins = $this->repository->getPinOwnerIds($user, $id);

        return $this->success($pins);
    }
}
