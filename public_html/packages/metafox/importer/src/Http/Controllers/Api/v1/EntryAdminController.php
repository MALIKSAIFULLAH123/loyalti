<?php

namespace MetaFox\Importer\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Importer\Http\Requests\v1\Entry\Admin\IndexRequest;
use MetaFox\Importer\Http\Requests\v1\Entry\Admin\StoreRequest;
use MetaFox\Importer\Http\Requests\v1\Entry\Admin\UpdateRequest;
use MetaFox\Importer\Http\Resources\v1\Entry\Admin\EntryDetail as Detail;
use MetaFox\Importer\Http\Resources\v1\Entry\Admin\EntryItemCollection as ItemCollection;
use MetaFox\Importer\Repositories\EntryRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Importer\Http\Controllers\Api\EntryAdminController::$controllers;.
 */

/**
 * Class EntryAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class EntryAdminController extends ApiController
{
    /**
     * @var EntryRepositoryInterface
     */
    private EntryRepositoryInterface $repository;

    /**
     * EntryAdminController Constructor.
     *
     * @param  EntryRepositoryInterface  $repository
     */
    public function __construct(EntryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest  $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $data = $this->repository->viewEntries($params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param  StoreRequest  $request
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
     * View item.
     *
     * @param  int  $id
     *
     * @return Detail
     */
    public function show($id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest  $request
     * @param  int            $id
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
     * Delete item.
     *
     * @param  int  $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->success([
            'id' => $id,
        ]);
    }
}
