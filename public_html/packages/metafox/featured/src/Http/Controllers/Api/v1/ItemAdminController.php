<?php

namespace MetaFox\Featured\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Featured\Http\Requests\v1\Item\Admin\UpdateSettingRequest;
use MetaFox\Featured\Services\Contracts\SettingServiceInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Featured\Http\Resources\v1\Item\Admin\ItemItemCollection as ItemCollection;
use MetaFox\Featured\Http\Resources\v1\Item\Admin\ItemDetail as Detail;
use MetaFox\Featured\Repositories\ItemRepositoryInterface;
use MetaFox\Featured\Http\Requests\v1\Item\Admin\IndexRequest;
use MetaFox\Featured\Http\Requests\v1\Item\Admin\StoreRequest;
use MetaFox\Featured\Http\Requests\v1\Item\Admin\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Featured\Http\Controllers\Api\ItemAdminController::$controllers;
 */

/**
 * Class ItemAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class ItemAdminController extends ApiController
{
    /**
     * @var ItemRepositoryInterface
     */
    private ItemRepositoryInterface $repository;

    /**
     * ItemAdminController Constructor
     *
     * @param  ItemRepositoryInterface $repository
     */
    public function __construct(ItemRepositoryInterface $repository)
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
        $data = $this->repository->paginate($params['limit'] ?? 100);

        return new ItemCollection($data);
    }

    /**
     * Store item
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): Detail
    {
        $params = $request->validated();
        $data = $this->repository->create($params);

        return new Detail($data);
    }

    /**
     * View item
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show($id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update item
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return Detail
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): Detail
    {
        $params = $request->validated();
        $data = $this->repository->update($params, $id);

        return new Detail($data);
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
        return $this->success([
            'id' => $id,
        ]);
    }


    public function updateSettings(UpdateSettingRequest $request): JsonResponse
    {
        $params = $request->validated();

        $role = resolve(RoleRepositoryInterface::class)->find(Arr::get($params, 'role_id'));

        $permissions = Arr::get($params, 'permissions');

        $context = user();

        resolve(SettingServiceInterface::class)->updateSettings($context, $role, $permissions);

        return $this->success([], [], __p('featured::admin.settings_was_updated_successfully'));
    }
}
