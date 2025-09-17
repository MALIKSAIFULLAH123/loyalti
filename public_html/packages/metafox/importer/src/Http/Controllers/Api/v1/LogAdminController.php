<?php

namespace MetaFox\Importer\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Importer\Http\Requests\v1\Log\Admin\IndexRequest;
use MetaFox\Importer\Http\Requests\v1\Log\Admin\StoreRequest;
use MetaFox\Importer\Http\Requests\v1\Log\Admin\UpdateRequest;
use MetaFox\Importer\Http\Resources\v1\Log\Admin\LogDetail as Detail;
use MetaFox\Importer\Http\Resources\v1\Log\Admin\LogItemCollection as ItemCollection;
use MetaFox\Importer\Repositories\LogRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Importer\Http\Controllers\Api\LogAdminController::$controllers;
 */

/**
 * Class LogAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class LogAdminController extends ApiController
{
    /**
     * @var LogRepositoryInterface
     */
    private LogRepositoryInterface $repository;

    /**
     * LogAdminController Constructor.
     *
     * @param  LogRepositoryInterface  $repository
     */
    public function __construct(LogRepositoryInterface $repository)
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
        $query = $this->repository->getModel()->newQuery();

        if (($level = $params['level'] ?? null)) {
            $query = $query->where('level_name', $level);
        }

        $data = $query->orderBy('id', 'desc')
            ->paginate($params['limit'] ?? 100);

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
