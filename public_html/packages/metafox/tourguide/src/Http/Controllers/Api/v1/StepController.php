<?php

namespace MetaFox\TourGuide\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\TourGuide\Http\Resources\v1\Step\StepDetail as Detail;
use MetaFox\TourGuide\Policies\TourGuidePolicy;
use MetaFox\TourGuide\Repositories\StepRepositoryInterface;
use MetaFox\TourGuide\Http\Requests\v1\Step\StoreRequest;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\TourGuide\Http\Controllers\Api\StepController::$controllers;.
 */

/**
 * Class StepController.
 * @codeCoverageIgnore
 * @ignore
 */
class StepController extends ApiController
{
    /**
     * @var StepRepositoryInterface
     */
    private StepRepositoryInterface $repository;

    /**
     * StepController Constructor.
     *
     * @param StepRepositoryInterface $repository
     */
    public function __construct(StepRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        policy_authorize(TourGuidePolicy::class, 'create', user());

        $resource = $this->repository->createStep($params);

        Artisan::call('cache:reset');

        return $this->success(new Detail($resource));
    }
}
