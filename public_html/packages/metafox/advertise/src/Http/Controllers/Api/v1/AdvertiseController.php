<?php

namespace MetaFox\Advertise\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MetaFox\Advertise\Http\Requests\v1\Advertise\IndexRequest;
use MetaFox\Advertise\Http\Requests\v1\Advertise\ReportRequest;
use MetaFox\Advertise\Http\Requests\v1\Advertise\ShowRequest;
use MetaFox\Advertise\Http\Requests\v1\Advertise\StoreRequest;
use MetaFox\Advertise\Http\Requests\v1\Advertise\UpdateRequest;
use MetaFox\Advertise\Http\Requests\v1\Advertise\UpdateTotalRequest;
use MetaFox\Advertise\Http\Resources\v1\Advertise\AdvertiseDetail as Detail;
use MetaFox\Advertise\Http\Resources\v1\Advertise\AdvertiseEmbed;
use MetaFox\Advertise\Http\Resources\v1\Advertise\AdvertiseEmbedCollection;
use MetaFox\Advertise\Http\Resources\v1\Advertise\AdvertiseItemCollection as ItemCollection;
use MetaFox\Advertise\Policies\AdvertisePolicy;
use MetaFox\Advertise\Repositories\AdvertiseRepositoryInterface;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Support\Facades\User as UserFacade;
use Prettus\Validator\Exceptions\ValidatorException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Advertise\Http\Controllers\Api\AdvertiseController::$controllers;
 */

/**
 * Class AdvertiseController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class AdvertiseController extends ApiController
{
    /**
     * @var AdvertiseRepositoryInterface
     */
    private AdvertiseRepositoryInterface $repository;

    /**
     * AdvertiseController Constructor.
     *
     * @param AdvertiseRepositoryInterface $repository
     */
    public function __construct(AdvertiseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param IndexRequest $request
     *
     * @return ItemCollection
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $context = user();

        if (!policy_check(AdvertisePolicy::class, 'viewAny', $context)) {
            return new ItemCollection([]);
        }

        $params = $request->validated();

        $data = $this->repository->viewAdvertises($context, $params);

        return new ItemCollection($data);
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        policy_authorize(AdvertisePolicy::class, 'create', $context);

        $data = $this->repository->createAdvertise($context, $params);

        $message = match ($data->status) {
            Support::ADVERTISE_STATUS_UNPAID  => __p('advertise::phrase.your_ad_has_successfully_been_submitted'),
            Support::ADVERTISE_STATUS_PENDING => __p('core::phrase.thanks_for_your_item_for_approval'),
            default                           => __p('advertise::phrase.ad_successfully_created')
        };

        return $this->success(new Detail($data), [], $message);
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $context = user();

        $data = $this->repository->viewAdvertise($context, $id);

        return $this->success(new Detail($data));
    }

    /**
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->updateAdvertise($context, $id, $params);

        return $this->success(new Detail($data), [], __p('advertise::phrase.ad_successfully_updated'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();

        $this->repository->deleteAdvertise($context, $id);

        return $this->success([
            'id' => $id,
        ], [], __p('advertise::phrase.ad_successfully_deleted'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function active(int $id): JsonResponse
    {
        $context = user();

        $isActive = (bool) match (MetaFox::isMobile()) {
            true  => request()->get('is_active', true),
            false => request()->get('active', true),
        };

        $this->repository->activeAdvertise($context, $id, $isActive);

        $message = match ($isActive) {
            true  => __p('advertise::phrase.ad_successfully_activated'),
            false => __p('advertise::phrase.ad_successfully_deactivated'),
        };

        return $this->success([
            'id'        => $id,
            'is_active' => $isActive,
        ], [], $message);
    }

    public function showAdvertises(ShowRequest $request): JsonResponse
    {
        $data = $request->validated();

        $placementId = Arr::get($data, 'placement_id');

        $location = Arr::get($data, 'location');

        $context = user();

        $advertises = $this->repository->showAdvertises($context, $placementId, $location);

        return $this->success(new AdvertiseEmbedCollection($advertises));
    }

    /**
     * @throws AuthenticationException
     */
    public function updateTotal(UpdateTotalRequest $request, int $id): JsonResponse
    {
        $context = Auth::guest() ? UserFacade::getGuestUser() : user();

        $data = $request->validated();

        $type = Arr::get($data, 'type');

        $advertise = $this->repository->updateTotal($context, $id, $type);

        if (null === $advertise) {
            return $this->success();
        }

        return $this->success(new AdvertiseEmbed($advertise));
    }

    public function getReport(ReportRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $result = $this->repository->viewReport($context, $id, Arr::get($data, 'view'), Arr::get($data, 'report_type'), Arr::get($data, 'date'));

        return $this->success($result);
    }

    public function hide(int $id): JsonResponse
    {
        $context = user();

        $this->repository->hideAdvertise($context, $id);

        return $this->success([], [], __p('advertise::phrase.ad_successfully_hidden'));
    }
}
