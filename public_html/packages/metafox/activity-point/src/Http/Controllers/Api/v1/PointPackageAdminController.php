<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Http\Requests\v1\PointPackage\Admin\IndexRequest;
use MetaFox\ActivityPoint\Http\Requests\v1\PointPackage\Admin\StoreRequest;
use MetaFox\ActivityPoint\Http\Requests\v1\PointPackage\Admin\UpdateRequest;
use MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\Admin\PointPackageDetail as Detail;
use MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\Admin\PointPackageItemCollection as ItemCollection;
use MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\Admin\StorePointPackageForm;
use MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\Admin\UpdatePointPackageForm;
use MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\ActivityPoint\Http\Controllers\Api\PointPackageAdminController::$controllers;
 */

/**
 * Class PointPackageAdminController.
 * @codeCoverageIgnore
 * @ignore
 * @authenticated
 * @group activitypoint
 * @admincp
 */
class PointPackageAdminController extends ApiController
{
    /**
     * @var PointPackageRepositoryInterface
     */
    private PointPackageRepositoryInterface $repository;

    /**
     * PointPackageAdminController Constructor.
     *
     * @param PointPackageRepositoryInterface $repository
     */
    public function __construct(PointPackageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $context = user();
        $params = $request->validated();
        $data = $this->repository->viewPackagesAdmin($context, $params);

        return $this->success(new ItemCollection($data));
    }

    public function create(Request $request): JsonResponse
    {
        $form = resolve(StorePointPackageForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters);
        }

        return $this->success($form);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();
        $params = $request->validated();

        $data = $this->repository->createPackage($context, $params);

        $this->navigate($data->admin_browse_url, true);

        return $this->success(
            new Detail($data),
            [],
            __p('activitypoint::phrase.point_package_created_successfully')
        );
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $context = user();
        $params = $request->validated();
        $data = $this->repository->updatePackage($context, $id, $params);

        $this->navigate($data->admin_browse_url, true);

        return $this->success(new Detail($data), [], __p('activitypoint::phrase.point_package_edited_successfully'));
    }

    public function edit(Request $request): JsonResponse
    {
        $form = resolve(UpdatePointPackageForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form);
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();

        $this->repository->deletePackage($context, $id);

        return $this->success([
            'id' => $id,
        ], [], __p('activitypoint::phrase.point_package_deleted_successfully'));
    }

    /**
     * @throws AuthenticationException
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $context = user();
        $params = $request->validated();

        $isActive = Arr::get($params, 'active', 1);

        $package = match ($isActive) {
            1       => $this->repository->activatePackage($context, $id),
            0       => $this->repository->deactivatePackage($context, $id),
            default => null,
        };

        if (null === $package) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'));
        }

        return $this->success(new Detail($package), [], __p('core::phrase.updated_successfully'));
    }
}
