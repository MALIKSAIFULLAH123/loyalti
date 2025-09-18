<?php

namespace MetaFox\Page\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Page\Http\Requests\v1\Block\IndexRequest;
use MetaFox\Page\Http\Requests\v1\Block\StoreRequest;
use MetaFox\Page\Http\Resources\v1\Block\BlockItemCollection as ItemCollection;
use MetaFox\Page\Repositories\BlockRepositoryInterface;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity;

class BlockController extends ApiController
{
    /**
     * @var BlockRepositoryInterface
     */
    private BlockRepositoryInterface $repository;

    /**
     * BlockController Constructor.
     *
     * @param BlockRepositoryInterface $repository
     */
    public function __construct(BlockRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data = $this->repository->viewPageBlocks(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request)
    {
        $params = $request->validated();
        $result = $this->repository->addPageBlock(user(), $params['page_id'], $params);
        $user = UserEntity::getById($params['user_id']);
        $userFullName = $user->name;

        if (!$result) {
            return $this->error(__p('page::phrase.member_does_not_exist'), 404);
        }

        $user = UserEntity::getById($params['user_id'])->detail;
        return $this->success([
            'user' => ResourceGate::asDetail($user),
        ], [], __p('page::phrase.user_full_name_has_been_blocked_from_the_page', ['user_full_name' => $userFullName]));
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function unblock(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->repository->deletePageBlock(user(), $params['page_id'], $params);

        $user = UserEntity::getById($params['user_id'])->detail;
        return $this->success([
            'user' => ResourceGate::asDetail($user),
        ], [], __p('page::phrase.member_has_been_unblocked'));
    }
}
