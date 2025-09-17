<?php

namespace MetaFox\Profile\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Profile\Http\Resources\v1\Profile\Admin\ProfileItemCollection as ItemCollection;
use MetaFox\Profile\Http\Resources\v1\Profile\Admin\ProfileDetail as Detail;
use MetaFox\Profile\Http\Resources\v1\Profile\Admin\CreateProfileForm;
use MetaFox\Profile\Http\Resources\v1\Profile\Admin\EditProfileForm;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Profile;
use MetaFox\Profile\Repositories\ProfileRepositoryInterface;
use MetaFox\Profile\Http\Requests\v1\Profile\Admin\IndexRequest;
use MetaFox\Profile\Http\Requests\v1\Profile\Admin\StoreRequest;
use MetaFox\Profile\Http\Requests\v1\Profile\Admin\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Profile\Http\Controllers\Api\ProfileAdminController::$controllers;
 */

/**
 * class ProfileAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class ProfileAdminController extends ApiController
{
    /**
     * @var ProfileRepositoryInterface
     */
    private ProfileRepositoryInterface $repository;

    /**
     * ProfileAdminController Constructor.
     *
     * @param ProfileRepositoryInterface $repository
     */
    public function __construct(ProfileRepositoryInterface $repository)
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
        $data   = $this->repository->paginate($params['limit'] ?? 100);

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
        $data   = $this->repository->create($params);

        $this->navigate('/profile/profile/browse');

        return $this->success(new Detail($data));
    }

    /**
     * View item.
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

    public function create()
    {
        $form = new CreateProfileForm();

        return $this->success($form);
    }

    public function edit(int $id)
    {
        $item  = $this->repository->find($id);
        $form  = new EditProfileForm($item);

        return $this->success($form);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->update($params, $id);

        $this->navigate('/profile/profile/browse');

        return $this->success(new Detail($data));
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
        /** @var Profile $item */
        $item = $this->repository->find($id);
        $item->delete();

        return $this->success([
            'id' => $id,
        ]);
    }
}
