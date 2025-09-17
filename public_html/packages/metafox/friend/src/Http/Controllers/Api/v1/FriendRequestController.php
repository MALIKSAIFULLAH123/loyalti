<?php

namespace MetaFox\Friend\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Friend\Http\Requests\v1\FriendRequest\IndexRequest;
use MetaFox\Friend\Http\Requests\v1\FriendRequest\StoreRequest;
use MetaFox\Friend\Http\Requests\v1\FriendRequest\UpdateRequest;
use MetaFox\Friend\Http\Resources\v1\FriendRequest\PendingCollection;
use MetaFox\Friend\Http\Resources\v1\FriendRequest\RequestSentCollection;
use MetaFox\Friend\Models\FriendRequest;
use MetaFox\Friend\Policies\FriendRequestPolicy;
use MetaFox\Friend\Repositories\FriendRequestRepositoryInterface;
use MetaFox\Friend\Support\Browse\Scopes\FriendRequest\ViewScope;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class FriendRequestController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group friend
 */
class FriendRequestController extends ApiController
{
    /**
     * @var FriendRequestRepositoryInterface
     */
    private FriendRequestRepositoryInterface $repository;

    /**
     * @param FriendRequestRepositoryInterface $repository
     */
    public function __construct(FriendRequestRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse friend request.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException|AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->viewRequests(user(), $params);

        $view = $params['view'];

        return $view == ViewScope::VIEW_SEND ? (new RequestSentCollection($data)) : (new PendingCollection($data));
    }

    /**
     * Create friend request.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws ValidatorException|AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $owner   = UserEntity::getById($params['friend_user_id'])->detail;

        // Check if whether context user has received request from owner
        $requested = $this->repository->getRequest($owner->entityId(), $context->entityId());

        if ($requested instanceof FriendRequest && !$requested->is_deny) {
            policy_authorize(FriendRequestPolicy::class, 'update', $owner);
            [$friendShip, $message] = $this->repository->updateRequest($owner, $context, FriendRequest::ACTION_APPROVE);

            return $this->success([
                'friendship' => $friendShip,
            ], [], $message);
        }
        
        policy_authorize(FriendRequestPolicy::class, 'sendRequest', user(), $owner);
        [$friendShip, $message] = $this->repository->sendRequest(user(), $owner);

        return $this->success([
            'friendship' => $friendShip,
        ], [], $message);
    }

    /**
     * Update friend request.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws ValidatorException|AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $user   = UserEntity::getById($id)->detail;
        $owner  = user();

        policy_authorize(FriendRequestPolicy::class, 'update', $owner);

        [$friendShip, $message] = $this->repository->updateRequest($user, $owner, $params['action']);

        return $this->success(ResourceGate::item($user->refresh(), false), [], $message);
    }

    /**
     * Remove friend request.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteRequestByUserIdAndOwnerId(user(), $id);
        $message = __p('friend::phrase.friend_request_canceled_successfully');
        $user    = UserEntity::getById($id)->detail;

        return $this->success(ResourceGate::item($user->refresh(), false), [], $message);
    }

    /**
     * Mark all as read.
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function markAllAsRead(): JsonResponse
    {
        $this->repository->markAllAsRead(user());

        return $this->success([]);
    }
}
