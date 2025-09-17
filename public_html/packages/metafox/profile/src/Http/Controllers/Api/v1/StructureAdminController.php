<?php

namespace MetaFox\Profile\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Profile\Http\Resources\v1\Structure\Admin\StructureItemCollection as ItemCollection;
use MetaFox\Profile\Http\Resources\v1\Structure\Admin\StructureDetail as Detail;
use MetaFox\Profile\Repositories\StructureRepositoryInterface;
use MetaFox\Profile\Http\Requests\v1\Structure\Admin\IndexRequest;
use MetaFox\Profile\Http\Requests\v1\Structure\Admin\StoreRequest;
use MetaFox\Profile\Http\Requests\v1\Structure\Admin\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Profile\Http\Controllers\Api\StructureAdminController::$controllers;
 */

/**
 * class StructureAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class StructureAdminController extends ApiController
{
    /**
     * @var StructureRepositoryInterface
     */
    private StructureRepositoryInterface $repository;

    /**
     * StructureAdminController Constructor.
     *
     * @param StructureRepositoryInterface $repository
     */
    public function __construct(StructureRepositoryInterface $repository)
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
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): Detail
    {
        $params = $request->validated();
        $data   = $this->repository->create($params);

        return new Detail($data);
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

    /**
     * Update item.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return Detail
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): Detail
    {
        $params = $request->validated();
        $data   = $this->repository->update($params, $id);

        return new Detail($data);
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
}
