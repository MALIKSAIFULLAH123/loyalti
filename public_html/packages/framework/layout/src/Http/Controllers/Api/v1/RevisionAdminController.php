<?php

namespace MetaFox\Layout\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Layout\Http\Requests\v1\Revision\Admin\IndexRequest;
use MetaFox\Layout\Http\Requests\v1\Revision\Admin\StoreRequest;
use MetaFox\Layout\Http\Requests\v1\Revision\Admin\UpdateRequest;
use MetaFox\Layout\Http\Resources\v1\Revision\Admin\RevisionDetail as Detail;
use MetaFox\Layout\Http\Resources\v1\Revision\Admin\RevisionItem;
use MetaFox\Layout\Http\Resources\v1\Revision\Admin\RevisionItemCollection as ItemCollection;
use MetaFox\Layout\Models\Revision;
use MetaFox\Layout\Repositories\RevisionRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Layout\Http\Controllers\Api\RevisionAdminController::$controllers;.
 */

/**
 * Class RevisionAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class RevisionAdminController extends ApiController
{
    /**
     * @var RevisionRepositoryInterface
     */
    private RevisionRepositoryInterface $repository;

    /**
     * RevisionAdminController Constructor.
     *
     * @param RevisionRepositoryInterface $repository
     */
    public function __construct(RevisionRepositoryInterface $repository)
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
        $data   = $this->repository->getModel()->newQuery()
            ->where('snippet_id', '=', $params['snippet'] ?? '')
            ->paginate($params['limit'] ?? 100);

        return new ItemCollection($data);
    }

    public function revert(int $id)
    {
        /** @var Revision $revision */
        $revision = $this->repository->find($id);

        $revision->revert();

        return $this->success(new RevisionItem($revision));
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
        $this->repository->find($id)->delete();

        return $this->success([
            'id' => $id,
        ]);
    }
}
