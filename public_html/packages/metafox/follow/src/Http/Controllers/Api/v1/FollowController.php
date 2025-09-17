<?php

namespace MetaFox\Follow\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Follow\Http\Requests\v1\Follow\IndexRequest;
use MetaFox\Follow\Http\Requests\v1\Follow\StoreRequest;
use MetaFox\Follow\Http\Resources\v1\Follow\FollowItemCollection as ItemCollection;
use MetaFox\Follow\Repositories\FollowRepositoryInterface;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Http\Resources\v1\User\UserStatistic;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Follow\Http\Controllers\Api\FollowController::$controllers;.
 */

/**
 * Class FollowController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class FollowController extends ApiController
{
    /**
     * FollowController Constructor.
     *
     * @param FollowRepositoryInterface $repository
     */
    public function __construct(protected FollowRepositoryInterface $repository)
    {
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $context = user();
        $userId  = Arr::get($params, 'user_id', $context->entityId());
        $user    = UserEntity::getById($userId)->detail;
        $data    = $this->repository->viewFollow($context, $params);

        $resources = new ItemCollection($data);

        $resources->setExtraMeta([
            'statistic' => new UserStatistic($user),
        ]);

        return $resources;
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $owner   = UserEntity::getById($params['user_id'])->detail;
        $this->repository->follow($context, $owner);

        return $this->success(ResourceGate::asDetail($owner), [], __p('follow::phrase.follow_successfully'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();
        $user    = UserEntity::getById($id)->detail;
        $this->repository->unfollow($context, $user);

        return $this->success(ResourceGate::asDetail($user), [], __p('follow::phrase.unfollowed_successfully'));
    }
}
