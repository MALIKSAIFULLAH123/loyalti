<?php

namespace MetaFox\Report\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Report\Http\Resources\v1\ReportItemAggregate\Admin\ReportItemAggregateItemCollection as ItemCollection;
use MetaFox\Report\Http\Resources\v1\ReportItemAggregate\Admin\ReportItemAggregateItem as Item;
use MetaFox\Report\Repositories\ReportItemAggregateAdminRepositoryInterface;
use MetaFox\Report\Http\Requests\v1\ReportItemAggregate\Admin\IndexRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Report\Http\Controllers\Api\ReportItemAggregateAdminController::$controllers;
 */

/**
 * Class ReportItemAggregateAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class ReportItemAggregateAdminController extends ApiController
{
    /**
     * @var ReportItemAggregateAdminRepositoryInterface
     */
    private ReportItemAggregateAdminRepositoryInterface $repository;

    /**
     * ReportItemAggregateAdminController Constructor.
     *
     * @param ReportItemAggregateAdminRepositoryInterface $repository
     */
    public function __construct(ReportItemAggregateAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest            $request
     * @return ItemCollection<Item>
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $user   = user();
        $params = $request->validated();
        $data   = $this->repository->viewAggregations($user, $params);

        return new ItemCollection($data);
    }

    /**
     * @throws AuthenticationException
     */
    public function process(int $id): JsonResponse
    {
        $context = user();
        $result  = $this->repository->processAggregation($context, $id);
        if ($result) {
            $this->repository->deleteAggregation($context, $id);
        }

        return $this->success([], [], __p('report::phrase.process_successfully'));
    }

    /**
     * @throws AuthenticationException
     */
    public function ignore(int $id): JsonResponse
    {
        //@todo: Should delete report permanently??
        $context = user();
        $this->repository->deleteAggregation($context, $id);

        return $this->success([], [], __p('report::phrase.ignore_report_successfully'));
    }
}
