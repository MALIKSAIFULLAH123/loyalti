<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Http\Requests\v1\PointSetting\Admin\IndexRequest;
use MetaFox\ActivityPoint\Http\Requests\v1\PointSetting\Admin\UpdateRequest;
use MetaFox\ActivityPoint\Http\Resources\v1\PointSetting\Admin\PointSettingDetail as Detail;
use MetaFox\ActivityPoint\Http\Resources\v1\PointSetting\Admin\PointSettingItemCollection as ItemCollection;
use MetaFox\ActivityPoint\Http\Resources\v1\PointSetting\Admin\UpdatePointSettingForm;
use MetaFox\ActivityPoint\Repositories\PointSettingRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;

/**
 * Class PointSettingAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class PointSettingAdminController extends ApiController
{
    /**
     * @var PointSettingRepositoryInterface
     */
    private PointSettingRepositoryInterface $repository;

    /**
     * PointSettingAdminController Constructor.
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
     * @return JsonResource
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResource
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->viewSettingsAdmin($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest           $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->updateSetting($context, $id, $params);

        return $this->success(new Detail($data), [], __p('activitypoint::phrase.point_setting_changes_have_been_saved_successfully'));
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
        return $this->success([
            'id' => $id,
        ]);
    }

    /**
     * @throws AuthenticationException
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $context = user();
        $params  = $request->validated();

        $isActive = Arr::get($params, 'active', 1);

        $package = match ($isActive) {
            1       => $this->repository->activateSetting($context, $id),
            0       => $this->repository->deactivateSetting($context, $id),
            default => null,
        };

        if (null === $package) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'));
        }

        return $this->success(new Detail($package), [], __p('core::phrase.updated_successfully'));
    }

    public function edit(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        return $this->success(new UpdatePointSettingForm($item));
    }

    public function create(): JsonResponse
    {
        return $this->success(new UpdatePointSettingForm());
    }
}
