<?php

namespace MetaFox\Advertise\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Advertise\Http\Resources\v1\Advertise\Admin\CreateAdvertiseForm;
use MetaFox\Advertise\Http\Resources\v1\Advertise\Admin\EditAdvertiseForm;
use MetaFox\Advertise\Policies\AdvertisePolicy;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Advertise\Http\Resources\v1\Advertise\Admin\AdvertiseItemCollection as ItemCollection;
use MetaFox\Advertise\Http\Resources\v1\Advertise\Admin\AdvertiseDetail as Detail;
use MetaFox\Advertise\Repositories\AdvertiseRepositoryInterface;
use MetaFox\Advertise\Http\Requests\v1\Advertise\Admin\IndexRequest;
use MetaFox\Advertise\Http\Requests\v1\Advertise\Admin\StoreRequest;
use MetaFox\Advertise\Http\Requests\v1\Advertise\Admin\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Advertise\Http\Controllers\Api\AdvertiseAdminController::$controllers;
 */

/**
 * Class AdvertiseAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class AdvertiseAdminController extends ApiController
{
    /**
     * @var AdvertiseRepositoryInterface
     */
    private AdvertiseRepositoryInterface $repository;

    /**
     * AdvertiseAdminController Constructor.
     *
     * @param AdvertiseRepositoryInterface $repository
     */
    public function __construct(AdvertiseRepositoryInterface $repository)
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

        $data   = $this->repository->viewAdvertiesForAdminCP($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data   = $this->repository->createAdvertiseAdminCP($context, $params);

        return $this->success(new Detail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => '/advertise/advertise/browse',
                ],
            ],
        ], __p('advertise::phrase.advertise_successfully_created'));
    }

    /**
     * @param  UpdateRequest                            $request
     * @param  int                                      $id
     * @return JsonResponse
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data   = $this->repository->updateAdvertiseAdminCP($context, $id, $params);

        return $this->success(new Detail($data), [], __p('advertise::phrase.advertise_successfully_updated'));
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

        $this->repository->deleteAdvertiseAdminCP($context, $id);

        return $this->success([
            'id' => $id,
        ], [], __p('advertise::phrase.advertise_successfully_deleted'));
    }

    /**
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function create(): JsonResponse
    {
        $context = user();

        policy_authorize(AdvertisePolicy::class, 'createAdminCP', $context);

        return $this->success(new CreateAdvertiseForm());
    }

    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $active = (bool) $request->get('active', true);

        $context = user();

        $this->repository->activeAdvertise($context, $id, $active);

        $message = match ($active) {
            true  => __p('advertise::phrase.advertise_successfully_activated'),
            false => __p('advertise::phrase.advertise_successfully_deactivated'),
        };

        return $this->success([], [], $message);
    }

    /**
     * @param  int          $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        $advertise = $this->repository->find($id);

        $form = new EditAdvertiseForm($advertise);

        return $this->success($form);
    }

    public function approve(int $id): JsonResponse
    {
        $context = user();

        $this->repository->approveAdvertise($context, $id);

        return $this->success([], [], __p('advertise::phrase.advertise_successfully_approved'));
    }

    public function deny(int $id): JsonResponse
    {
        $context = user();

        $this->repository->denyAdvertise($context, $id);

        return $this->success([], [], __p('advertise::phrase.advertise_successfully_denied'));
    }

    public function markAsPaid(int $id): JsonResponse
    {
        $context = user();

        $this->repository->markAsPaid($context, $id);

        return $this->success([], [], __p('advertise::phrase.ad_successfully_marked_as_paid'));
    }
}
