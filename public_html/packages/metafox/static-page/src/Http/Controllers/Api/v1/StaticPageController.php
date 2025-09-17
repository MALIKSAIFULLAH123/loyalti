<?php

namespace MetaFox\StaticPage\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\StaticPage\Http\Requests\v1\StaticPage\IndexRequest;
use MetaFox\StaticPage\Http\Requests\v1\StaticPage\StoreRequest;
use MetaFox\StaticPage\Http\Requests\v1\StaticPage\UpdateRequest;
use MetaFox\StaticPage\Http\Resources\v1\StaticPage\StaticPageDetail as Detail;
use MetaFox\StaticPage\Http\Resources\v1\StaticPage\StaticPageItemCollection as ItemCollection;
use MetaFox\StaticPage\Repositories\StaticPageRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\StaticPage\Http\Controllers\Api\StaticPageController::$controllers.
 */

/**
 * Class StaticPageController.
 * @codeCoverageIgnore
 * @ignore
 */
class StaticPageController extends ApiController
{
    /**
     * @var StaticPageRepositoryInterface
     */
    private StaticPageRepositoryInterface $repository;

    /**
     * StaticPageController Constructor.
     *
     * @param StaticPageRepositoryInterface $repository
     */
    public function __construct(StaticPageRepositoryInterface $repository)
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
    public function show(int $id): Detail
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
