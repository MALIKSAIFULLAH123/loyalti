<?php

namespace MetaFox\Subscription\Http\Controllers\Api\v1;

use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\IndexRequest;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\SubscriptionCancelReasonItemCollection as ItemCollection;
use MetaFox\Subscription\Repositories\SubscriptionCancelReasonRepositoryInterface;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Subscription\Http\Controllers\Api\SubscriptionCancelReasonController::$controllers;
 */

/**
 * Class SubscriptionCancelReasonController.
 * @codeCoverageIgnore
 * @ignore
 */
class SubscriptionCancelReasonController extends ApiController
{
    /**
     * @var SubscriptionCancelReasonRepositoryInterface
     */
    private SubscriptionCancelReasonRepositoryInterface $repository;

    /**
     * SubscriptionCancelReasonController Constructor.
     *
     * @param SubscriptionCancelReasonRepositoryInterface $repository
     */
    public function __construct(SubscriptionCancelReasonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->viewReasons($context, $params);

        return new ItemCollection($data);
    }
}
