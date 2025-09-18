<?php

namespace MetaFox\Subscription\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionComparison\IndexRequest;
use MetaFox\Subscription\Repositories\SubscriptionComparisonRepositoryInterface;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
use MetaFox\User\Support\Facades\User;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Subscription\Http\Controllers\Api\SubscriptionComparisonController::$controllers;
 */

/**
 * Class SubscriptionComparisonController.
 * @codeCoverageIgnore
 * @ignore
 */
class SubscriptionComparisonController extends ApiController
{
    /**
     * @var SubscriptionComparisonRepositoryInterface
     */
    private SubscriptionComparisonRepositoryInterface $repository;

    /**
     * SubscriptionComparisonController Constructor.
     *
     * @param SubscriptionComparisonRepositoryInterface $repository
     */
    public function __construct(SubscriptionComparisonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = User::getGuestUser();

        if (Auth::id() != MetaFoxConstant::GUEST_USER_ID) {
            $context = user();
        }

        if (!SubscriptionPackage::hasPackages()) {
            return $this->success();
        }

        $data = $this->repository->viewComparisons($context, $params);

        return $this->success($data);
    }
}
