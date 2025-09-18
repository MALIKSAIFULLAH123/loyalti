<?php

namespace MetaFox\LiveStreaming\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\IndexRequest;
use MetaFox\LiveStreaming\Http\Requests\v1\ServiceAccount\Admin\StoreRequest;
use MetaFox\LiveStreaming\Http\Requests\v1\ServiceAccount\Admin\UpdateRequest;
use MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo\LiveVideoDetail as Detail;
use MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo\LiveVideoItemCollection as ItemCollection;
use MetaFox\LiveStreaming\Repositories\ServiceAccountRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\LiveStreaming\Http\Controllers\Api\ServiceAccountAdminController::$controllers;.
 */

/**
 * Class LiveVideoController.
 * @codeCoverageIgnore
 * @ignore
 */
class ServiceAccountAdminController extends ApiController
{
    /**
     * @var ServiceAccountRepositoryInterface
     */
    private ServiceAccountRepositoryInterface $repository;

    /**
     * LiveVideoController Constructor.
     *
     * @param ServiceAccountRepositoryInterface $repository
     */
    public function __construct(ServiceAccountRepositoryInterface $repository)
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

        $this->repository->create($params);

        $nextAction = ['type' => 'navigate', 'payload' => ['url' => '/livestreaming/setting']];

        return $this->success([], ['nextAction' => $nextAction]);
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
