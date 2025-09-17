<?php

namespace MetaFox\Search\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Search\Http\Requests\v1\Type\Admin\IndexRequest;
use MetaFox\Search\Http\Requests\v1\Type\Admin\StoreRequest;
use MetaFox\Search\Http\Requests\v1\Type\Admin\UpdateRequest;
use MetaFox\Search\Http\Resources\v1\Type\Admin\TypeDetail as Detail;
use MetaFox\Search\Http\Resources\v1\Type\Admin\TypeItemCollection as ItemCollection;
use MetaFox\Search\Repositories\TypeRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Search\Http\Controllers\Api\TypeAdminController::$controllers;
 */

/**
 * Class TypeAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @group admincp/search
 */
class TypeAdminController extends ApiController
{
    /**
     * @var TypeRepositoryInterface
     */
    public TypeRepositoryInterface $repository;

    public function __construct(TypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->create($params);

        return new Detail($data);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show($id)
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return Detail
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, $id)
    {
        $params = $request->validated();
        $data   = $this->repository->update($params, $id);

        return new Detail($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return $this->success([
            'id' => $id,
        ]);
    }
}
