<?php

namespace MetaFox\Featured\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Featured\Http\Resources\v1\Package\Admin\CreatePackageForm;
use MetaFox\Featured\Http\Resources\v1\Package\Admin\EditPackageForm;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Featured\Http\Resources\v1\Package\Admin\PackageItemCollection as ItemCollection;
use MetaFox\Featured\Http\Resources\v1\Package\Admin\PackageDetail as Detail;
use MetaFox\Featured\Repositories\PackageRepositoryInterface;
use MetaFox\Featured\Http\Requests\v1\Package\Admin\IndexRequest;
use MetaFox\Featured\Http\Requests\v1\Package\Admin\StoreRequest;
use MetaFox\Featured\Http\Requests\v1\Package\Admin\UpdateRequest;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Featured\Http\Controllers\Api\PackageAdminController::$controllers;
 */

/**
 * Class PackageAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class PackageAdminController extends ApiController
{
    /**
     * @var PackageRepositoryInterface
     */
    private PackageRepositoryInterface $repository;

    /**
     * PackageAdminController Constructor
     *
     * @param  PackageRepositoryInterface $repository
     */
    public function __construct(PackageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $data = $this->repository->viewAdmincpPackages($params);

        return new ItemCollection($data);
    }

    /**
     * Store item
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $package = $this->repository->createPackage($params);

        return $this->success(new Detail($package), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url'     => '/featured/package/browse',
                    'replace' => true,
                ],
            ]
        ], __p('featured::admin.package_was_created_successfully'));
    }

    /**
     * Update item
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $package = $this->repository->find($id);

        $package = $this->repository->updatePackage($package, $params);

        return $this->success(new Detail($package), [], __p('featured::admin.package_was_updated_successfully'));
    }

    /**
     * Delete item
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $package = $this->repository->find($id);

        $this->repository->deletePackage($package);

        return $this->success([], [], __p('featured::admin.package_was_deleted_successfully'));
    }

    /**
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        return $this->success(new CreatePackageForm());
    }

    public function edit(int $id): JsonResponse
    {
        $package = $this->repository->find($id);

        return $this->success(new EditPackageForm($package));
    }

    /**
     * @param  ActiveRequest $request
     * @param  int           $id
     * @return JsonResponse
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $package = $this->repository->find($id);

        $isActive = (bool) Arr::get($data, 'active', false);

        $package->update(['is_active' => $isActive]);

        $message = match ($isActive) {
            true    => __p('featured::admin.package_was_activated_successfully'),
            default =>  __p('featured::admin.package_was_deactivated_successfully'),
        };

        return $this->success(new Detail($package), [], $message);
    }
}
