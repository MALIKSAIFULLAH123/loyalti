<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Repositories\ConversionRequestRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest\Admin\ConversionRequestItemCollection as ItemCollection;
use MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest\Admin\ConversionRequestDetail as Detail;
use MetaFox\ActivityPoint\Http\Requests\v1\ConversionRequest\Admin\IndexRequest;
use MetaFox\ActivityPoint\Http\Requests\v1\ConversionRequest\Admin\DenyRequest;
use MetaFox\ActivityPoint\Policies\ConversionRequestPolicy;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\ActivityPoint\Http\Controllers\Api\ConversionRequestAdminController::$controllers;
 */

/**
 * Class ConversionRequestAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class ConversionRequestAdminController extends ApiController
{
    /**
     * @var ConversionRequestRepositoryInterface
     */
    private ConversionRequestRepositoryInterface $repository;

    /**
     * ConversionRequestAdminController Constructor
     *
     * @param  ConversionRequestRepositoryInterface $repository
     */
    public function __construct(ConversionRequestRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $data = $this->repository->viewConversionRequestAdminCP($params);

        return new ItemCollection($data);
    }

    public function approve(int $id): JsonResponse
    {
        $request = $this->repository->find($id);

        $context = user();

        policy_authorize(ConversionRequestPolicy::class, 'approveConversionRequest', $context, $request);

        $request = $this->repository->approveConversionRequest($context, $request);

        return $this->success(new Detail($request), [], __p('activitypoint::admin.request_was_approved_successfully'));
    }

    public function deny(DenyRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $request = $this->repository->find($id);

        $context = user();

        policy_authorize(ConversionRequestPolicy::class, 'denyConversionRequest', $context, $request);

        $request = $this->repository->denyConversionRequest($context, $request, Arr::get($params, 'reason'));

        return $this->success(new Detail($request), [], __p('activitypoint::admin.request_was_denied_successfully'));
    }
}
