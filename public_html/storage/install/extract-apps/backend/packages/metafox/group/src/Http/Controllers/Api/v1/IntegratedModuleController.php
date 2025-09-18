<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Group\Http\Requests\v1\IntegratedModule\IndexRequest;
use MetaFox\Group\Http\Requests\v1\IntegratedModule\OrderingRequest;
use MetaFox\Group\Http\Resources\v1\Group\GroupDetail;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Group\Repositories\IntegratedModuleRepositoryInterface;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Group\Http\Controllers\Api\IntegratedModuleController::$controllers;
 */

/**
 * Class IntegratedModuleController.
 * @codeCoverageIgnore
 * @ignore
 */
class IntegratedModuleController extends ApiController
{
    /**
     * @var IntegratedModuleRepositoryInterface
     */
    private IntegratedModuleRepositoryInterface $repository;

    /**
     * IntegratedModuleController Constructor.
     *
     * @param IntegratedModuleRepositoryInterface $repository
     */
    public function __construct(
        IntegratedModuleRepositoryInterface $repository,
        protected GroupRepositoryInterface $groupRepository
    )
    {
        $this->repository = $repository;
    }

    /**
     * Update item.
     *
     * @param  Request                 $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->repository->updateModule(user(), $id, $request->all());
        $group = $this->groupRepository->find($id);

        return $this->success(new GroupDetail($group), [], __p('group::phrase.setting_successfully_updated'));
    }

    /**
     * Reorder example rule.
     *
     * @param OrderingRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function order(OrderingRequest $request): JsonResponse
    {
        $params = $request->validated();

        $this->repository->orderModules(user(), $params);

        return $this->success([], [], __p('group::phrase.setting_successfully_updated'));
    }
}
