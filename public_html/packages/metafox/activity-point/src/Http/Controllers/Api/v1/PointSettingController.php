<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\ActivityPoint\Http\Requests\v1\PointSetting\IndexRequest;
use MetaFox\ActivityPoint\Http\Resources\v1\PointSetting\PointSettingByModule as ItemsGroupBy;
use MetaFox\ActivityPoint\Repositories\PointSettingRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\ActivityPoint\Http\Controllers\Api\PointSettingController::$controllers;
 */

/**
 * Class PointSettingController.
 * @codeCoverageIgnore
 * @ignore
 * @authenticated
 * @group activitypoint
 */
class PointSettingController extends ApiController
{
    /**
     * @var PointSettingRepositoryInterface
     */
    private PointSettingRepositoryInterface $repository;

    /**
     * PointSettingController Constructor.
     *
     * @param PointSettingRepositoryInterface $repository
     */
    public function __construct(PointSettingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest            $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->viewSettings($context, $params);

        return $this->success(new ItemsGroupBy($data));
    }
}
