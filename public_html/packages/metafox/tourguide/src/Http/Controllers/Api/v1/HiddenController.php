<?php

namespace MetaFox\TourGuide\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\TourGuide\Http\Requests\v1\Hidden\DestroyRequest;
use MetaFox\TourGuide\Policies\TourGuidePolicy;
use MetaFox\TourGuide\Repositories\HiddenRepositoryInterface;
use MetaFox\TourGuide\Http\Requests\v1\Hidden\StoreRequest;
use MetaFox\TourGuide\Repositories\TourGuideRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\TourGuide\Http\Controllers\Api\HiddenController::$controllers;.
 */

/**
 * Class HiddenController.
 * @codeCoverageIgnore
 * @ignore
 */
class HiddenController extends ApiController
{
    private HiddenRepositoryInterface $repository;
    private TourGuideRepositoryInterface $tourGuideRepository;

    public function __construct(HiddenRepositoryInterface $repository, TourGuideRepositoryInterface $tourGuideRepository)
    {
        $this->repository          = $repository;
        $this->tourGuideRepository = $tourGuideRepository;
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

        $tourGuide = $this->tourGuideRepository->find(Arr::get($params, 'tour_guide_id'));

        policy_authorize(TourGuidePolicy::class, 'view', $context, $tourGuide);

        $this->repository->createHidden(
            $context->entityId(),
            $tourGuide->entityId(),
        );

        return $this->success();
    }

    /**
     * @param DestroyRequest $request
     * @return JsonResponse
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function destroy(DestroyRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $tourGuide = $this->tourGuideRepository->find(Arr::get($params, 'tour_guide_id'));

        $this->repository->deleteHidden(
            $context->entityId(),
            $tourGuide->entityId(),
        );

        return $this->success();
    }
}
