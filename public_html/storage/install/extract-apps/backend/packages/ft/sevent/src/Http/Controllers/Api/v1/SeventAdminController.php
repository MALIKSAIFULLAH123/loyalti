<?php

namespace Foxexpert\Sevent\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Foxexpert\Sevent\Http\Requests\v1\Sevent\IndexRequest;
use Foxexpert\Sevent\Http\Requests\v1\Sevent\StoreRequest;
use Foxexpert\Sevent\Http\Requests\v1\Sevent\UpdateRequest;
use Foxexpert\Sevent\Http\Resources\v1\Sevent\SeventDetail as Detail;
use Foxexpert\Sevent\Http\Resources\v1\Sevent\SeventItemCollection as ItemCollection;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 | stub: /packages/controllers/admin_api_controller.stub
 | Assign this class in $controllers of
 | @link \Foxexpert\Sevent\Http\Controllers\Api\SeventController::$controllers;
 */

/**
 * Class SeventAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group sevent
 */
class SeventAdminController extends ApiController
{
    /**
     * @var SeventRepositoryInterface
     */
    private SeventRepositoryInterface $repository;

    /**
     * @param SeventRepositoryInterface $repository
     */
    public function __construct(SeventRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse sevents.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->get($params);

        return new ItemCollection($data);
    }

    /**
     * Create sevent.
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
     * View sevent.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update sevent.
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
     * Remove sevent.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return $this->success([
            'id' => $id,
        ]);
    }
}
