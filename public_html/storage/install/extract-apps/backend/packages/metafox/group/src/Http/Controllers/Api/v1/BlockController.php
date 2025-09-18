<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Group\Http\Requests\v1\Block\IndexRequest;
use MetaFox\Group\Http\Requests\v1\Block\StoreRequest;
use MetaFox\Group\Http\Resources\v1\Block\BlockItemCollection as ItemCollection;
use MetaFox\Group\Repositories\BlockRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Group\Http\Controllers\Api\BlockController::$controllers;
 */

/**
 * Class BlockController
 * @codeCoverageIgnore
 * @ignore
 */
class BlockController extends ApiController
{
    /**
     * @var BlockRepositoryInterface
     */
    private BlockRepositoryInterface $repository;

    /**
     * BlockController Constructor
     *
     * @param BlockRepositoryInterface $repository
     */
    public function __construct(BlockRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewGroupBlocks(user(), $params);
        return new ItemCollection($data);
    }

    /**
     * Store item
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params       = $request->validated();
        $result       = $this->repository->addGroupBlock(user(), $params['group_id'], $params);
        $user         = UserEntity::getById($params['user_id']);
        $userFullName = $user->name;

        if (!$result) {
            return $this->error(__p('group::phrase.member_does_not_exist'), 404);
        }

        return $this->success([
            'user_id' => $params['user_id'],
        ], [], __p('group::phrase.user_full_name_has_been_blocked_from_the_group', ['user_full_name' => $userFullName]));
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function unblock(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->repository->deleteGroupBlock(user(), $params['group_id'], $params);

        return $this->success([
            'user' => UserEntity::getById($params['user_id'])->detail,
        ], [], __p('group::phrase.member_has_been_unblocked'));
    }
}
