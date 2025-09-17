<?php

namespace MetaFox\BackgroundStatus\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use MetaFox\BackgroundStatus\Http\Requests\v1\BgsCollection\Admin\DeleteRequest;
use MetaFox\BackgroundStatus\Http\Requests\v1\BgsCollection\Admin\IndexRequest;
use MetaFox\BackgroundStatus\Http\Requests\v1\BgsCollection\Admin\StoreRequest;
use MetaFox\BackgroundStatus\Http\Requests\v1\BgsCollection\Admin\UpdateRequest;
use MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection\Admin\BgsCollectionItem as Detail;
use MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection\Admin\BgsCollectionItemCollection as ItemCollection;
use MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection\Admin\StoreCollectionForm;
use MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection\Admin\UpdateCollectionForm;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\BackgroundStatus\Http\Controllers\Api\BgsCollectionAdminController::$controllers;.
 */

/**
 * Class BgsCollectionAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class BgsCollectionAdminController extends ApiController
{
    /**
     * @var BgsCollectionRepositoryInterface
     */
    private BgsCollectionRepositoryInterface $repository;

    /**
     * BgsCollectionAdminController Constructor.
     *
     * @param BgsCollectionRepositoryInterface $repository
     */
    public function __construct(BgsCollectionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewBgsCollectionsForAdmin(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createBgsCollection(user(), $params);
        $this->navigate($data->admin_browse_url);

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('backgroundstatus::phrase.created_successfully'));
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateBgsCollection(user(), $id, $params);
        $this->navigate($data->admin_browse_url);
        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('backgroundstatus::phrase.updated_successfully'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteBgsCollection(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('backgroundstatus::phrase.deleted_successfully'));
    }

    public function toggleActive(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        $item->update(['is_active' => $item->is_active ? 0 : 1]);

        return $this->success(
            new Detail($item),
            [],
            __p('core::phrase.already_saved_changes')
        );
    }

    public function create(): StoreCollectionForm
    {
        return new StoreCollectionForm();
    }

    public function edit(int $id): UpdateCollectionForm
    {
        $item = $this->repository->find($id);

        return new UpdateCollectionForm($item);
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function batchDelete(DeleteRequest $request): JsonResponse
    {
        $params = $request->validated();
        $ids    = Arr::get($params, 'id', []);

        foreach ($ids as $id) {
            $this->repository->deleteBgsCollection(user(), $id);
        }

        return $this->success([], [], __p('backgroundstatus::phrase.deleted_successfully'));
    }
}
