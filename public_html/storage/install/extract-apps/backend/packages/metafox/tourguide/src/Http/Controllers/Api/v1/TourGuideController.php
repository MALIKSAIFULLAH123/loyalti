<?php

namespace MetaFox\TourGuide\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\TourGuide\Http\Requests\v1\TourGuide\GetActionsRequest;
use MetaFox\TourGuide\Http\Requests\v1\TourGuide\StoreRequest;
use MetaFox\TourGuide\Http\Resources\v1\TourGuide\TourGuideDetail as Detail;
use MetaFox\TourGuide\Policies\TourGuidePolicy;
use MetaFox\TourGuide\Repositories\TourGuideRepositoryInterface;
use MetaFox\User\Support\Facades\User as Facade;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\TourGuide\Http\Controllers\Api\TourGuideController::$controllers;.
 */

/**
 * Class TourGuideController.
 * @codeCoverageIgnore
 * @ignore
 */
class TourGuideController extends ApiController
{
    /**
     * @var TourGuideRepositoryInterface
     */
    private TourGuideRepositoryInterface $repository;

    /**
     * TourGuideController Constructor.
     *
     * @param TourGuideRepositoryInterface $repository
     */
    public function __construct(TourGuideRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function show(int $id): JsonResponse
    {
        $resource = $this->repository->find($id);

        policy_authorize(TourGuidePolicy::class, 'view', user(), $resource);

        return $this->success(new Detail($resource));
    }

    /**
     * @param GetActionsRequest $request
     * @return JsonResponse
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function getActions(GetActionsRequest $request): JsonResponse
    {
        $context = user();
        if (Facade::hasPendingSubscription($request, $context)) {
            return $this->success([]);
        }

        $params = $request->validated();

        $actions = $this->repository->getActions($context, $params);

        return $this->success($actions);
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        policy_authorize(TourGuidePolicy::class, 'create', $context);

        $resource = $this->repository->createTourGuide($context, $params);

        return $this->success(
            new Detail($resource),
            [],
            __p('tourguide::phrase.tour_guide_was_created_successfully')
        );
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function active(int $id): JsonResponse
    {
        $context  = user();
        $resource = $this->repository->find($id);

        policy_authorize(TourGuidePolicy::class, 'active', $context, $resource);

        $this->repository->updateTourGuide($id, ['is_active' => true]);

        return $this->success(
            new Detail($resource->refresh()),
            [],
            __p('tourguide::phrase.tour_guide_was_marked_as_completed_successfully')
        );
    }
}
