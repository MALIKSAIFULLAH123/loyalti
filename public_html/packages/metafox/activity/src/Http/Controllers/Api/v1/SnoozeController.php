<?php

namespace MetaFox\Activity\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Activity\Http\Requests\v1\Snooze\DeleteRequest;
use MetaFox\Activity\Http\Requests\v1\Snooze\IndexRequest;
use MetaFox\Activity\Http\Requests\v1\Snooze\StoreRequest;
use MetaFox\Activity\Http\Resources\v1\Snooze\SnoozeDetail;
use MetaFox\Activity\Http\Resources\v1\Snooze\SnoozeItemCollection as ItemCollection;
use MetaFox\Activity\Policies\SnoozePolicy;
use MetaFox\Activity\Repositories\SnoozeRepositoryInterface;
use MetaFox\Activity\Support\Facades\Snooze;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Activity\Http\Controllers\Api\SnoozeController::$controllers;
 */

/**
 * Class SnoozeController.
 * @ignore
 * @codeCoverageIgnore
 * @group feed
 * @authenticated
 */
class SnoozeController extends ApiController
{
    public function __construct(protected SnoozeRepositoryInterface $repository)
    {
    }

    /**
     * @param  IndexRequest                             $request
     * @return JsonResponse
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $resource = $this->repository->getSnoozes($context, $params);

        return $this->success(new ItemCollection($resource));
    }

    /**
     * @param  StoreRequest                                   $request
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $owner = UserEntity::getById(Arr::get($params, 'user_id'))->detail;

        policy_authorize(SnoozePolicy::class, 'snooze', $context, $owner);

        Snooze::snooze($context, $owner);

        return $this->success();
    }

    /**
     * @param  StoreRequest                                   $request
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function snoozeForever(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $owner = UserEntity::getById(Arr::get($params, 'user_id'))->detail;

        policy_authorize(SnoozePolicy::class, 'snoozeForever', $context, $owner);

        $snooze = Snooze::snoozeForever($context, $owner);

        return $this->success(
            new SnoozeDetail($snooze),
            [],
            __p('activity::phrase.hide_all_successfully')
        );
    }

    /**
     * @param  DeleteRequest                                  $request
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function destroy(DeleteRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $owner = UserEntity::getById(Arr::get($params, 'user_id'))->detail;

        policy_authorize(SnoozePolicy::class, 'unSnooze', $context, $owner);

        $snooze = Snooze::unSnooze($context, $owner);

        return $this->success(
            new SnoozeDetail($snooze),
            [],
            __p('activity::phrase.unhide_all_successfully')
        );
    }
}
