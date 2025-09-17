<?php

namespace MetaFox\StaticPage\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\StaticPage\Http\Requests\v1\StaticPage\Admin\IndexRequest;
use MetaFox\StaticPage\Http\Requests\v1\StaticPage\Admin\StoreRequest;
use MetaFox\StaticPage\Http\Requests\v1\StaticPage\Admin\UpdateRequest;
use MetaFox\StaticPage\Http\Resources\v1\StaticPage\Admin\CreateStaticPageForm;
use MetaFox\StaticPage\Http\Resources\v1\StaticPage\Admin\EditStaticPageForm;
use MetaFox\StaticPage\Http\Resources\v1\StaticPage\Admin\StaticPageDetail as Detail;
use MetaFox\StaticPage\Http\Resources\v1\StaticPage\Admin\StaticPageItemCollection as ItemCollection;
use MetaFox\StaticPage\Repositories\StaticPageRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\StaticPage\Http\Controllers\Api\StaticPageAdminController::$controllers.
 */

/**
 * Class StaticPageAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class StaticPageAdminController extends ApiController
{
    /**
     * @var StaticPageRepositoryInterface
     */
    private StaticPageRepositoryInterface $repository;

    /**
     * StaticPageAdminController Constructor.
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
        $data   = $this->repository->paginate($params['limit'] ?? 50);

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
        $data   = $this->repository->createStaticPage($params);

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url'     => '/static-page/page/browse',
                    'replace' => true,
                ],
            ],
        ], __p('static-page::phrase.static_page_created_successfully'));
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
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateStaticPage($id, $params);

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url'     => '/static-page/page/browse',
                    'replace' => true,
                ],
            ],
        ], __p('static-page::phrase.static_page_updated_successfully'));
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
        $this->repository->deleteStaticPage($id);

        return $this->success([
            'id' => $id,
        ], [], __p('static-page::phrase.static_page_deleted_successfully'));
    }

    public function edit(int $id): JsonResponse
    {
        $entity = $this->repository->find($id);

        return $this->success(new EditStaticPageForm($entity));
    }

    public function create(): JsonResponse
    {
        return $this->success(new CreateStaticPageForm());
    }
}
