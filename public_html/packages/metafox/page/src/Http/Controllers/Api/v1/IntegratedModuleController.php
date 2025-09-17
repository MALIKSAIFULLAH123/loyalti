<?php

namespace MetaFox\Page\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Page\Http\Resources\v1\Page\PageDetail;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Page\Http\Resources\v1\IntegratedModule\IntegratedModuleItemCollection as ItemCollection;
use MetaFox\Page\Http\Resources\v1\IntegratedModule\IntegratedModuleDetail as Detail;
use MetaFox\Page\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Page\Http\Requests\v1\IntegratedModule\IndexRequest;
use MetaFox\Page\Http\Requests\v1\IntegratedModule\OrderingRequest;
use MetaFox\Page\Http\Requests\v1\IntegratedModule\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Page\Http\Controllers\Api\IntegratedModuleController::$controllers;
 */

/**
 * Class IntegratedModuleController.
 * @codeCoverageIgnore
 * @ignore
 */
class IntegratedModuleController extends ApiController
{
    /**
     * IntegratedModuleController Constructor.
     *
     * @param IntegratedModuleRepositoryInterface $repository
     * @param PageRepositoryInterface             $pageRepository
     */
    public function __construct(
        protected IntegratedModuleRepositoryInterface $repository,
        protected PageRepositoryInterface $pageRepository
    ) {
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
        $page = $this->pageRepository->find($id);

        return $this->success(new PageDetail($page), [], __p('page::phrase.setting_successfully_updated'));
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

        return $this->success([], [], __p('page::phrase.setting_successfully_updated'));
    }
}
