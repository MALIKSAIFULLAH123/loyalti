<?php

namespace MetaFox\Queue\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Queue\Http\Requests\v1\FailedJob\Admin\IndexRequest;
use MetaFox\Queue\Http\Requests\v1\FailedJob\Admin\StoreRequest;
use MetaFox\Queue\Http\Requests\v1\FailedJob\Admin\UpdateRequest;
use MetaFox\Queue\Http\Resources\v1\FailedJob\Admin\FailedJobDetail as Detail;
use MetaFox\Queue\Http\Resources\v1\FailedJob\Admin\FailedJobItemCollection as ItemCollection;
use MetaFox\Queue\Models\FailedJob;
use MetaFox\Queue\Repositories\FailedJobRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Queue\Http\Controllers\Api\FailedJobAdminController::$controllers.
 */

/**
 * Class FailedJobAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class FailedJobAdminController extends ApiController
{
    /**
     * @var FailedJobRepositoryInterface
     */
    private FailedJobRepositoryInterface $repository;

    /**
     * FailedJobAdminController Constructor.
     *
     * @param FailedJobRepositoryInterface $repository
     */
    public function __construct(FailedJobRepositoryInterface $repository)
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

    public function retry(int $id): JsonResponse
    {
        /** @var FailedJob $job */
        $job = $this->repository->find($id);

        Artisan::call('queue:retry', [
            'id' => $job->uuid,
        ]);

        $message = Artisan::output();

        return $this->success([
            'id' => $id,
        ], [], $message);
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
        /** @var FailedJob $job */
        $job = $this->repository->find($id);

        $job->delete();

        return $this->success([
            'id' => $id,
        ]);
    }
}
