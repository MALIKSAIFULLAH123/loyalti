<?php

namespace MetaFox\Layout\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Layout\Http\Requests\v1\Snippet\Admin\IndexRequest;
use MetaFox\Layout\Http\Requests\v1\Snippet\Admin\StoreRequest;
use MetaFox\Layout\Http\Requests\v1\Snippet\Admin\UpdateRequest;
use MetaFox\Layout\Http\Resources\v1\Snippet\Admin\SnippetDetail as Detail;
use MetaFox\Layout\Http\Resources\v1\Snippet\Admin\SnippetItemCollection as ItemCollection;
use MetaFox\Layout\Models\Snippet;
use MetaFox\Layout\Models\Theme;
use MetaFox\Layout\Repositories\RevisionRepositoryInterface;
use MetaFox\Layout\Repositories\SnippetRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Layout\Http\Controllers\Api\SnippetAdminController::$controllers
 */

/**
 * Class SnippetAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class SnippetAdminController extends ApiController
{
    /**
     * @var SnippetRepositoryInterface
     */
    private SnippetRepositoryInterface $repository;
    private RevisionRepositoryInterface $revisionRepository;

    /**
     * SnippetAdminController Constructor.
     *
     * @param  SnippetRepositoryInterface  $repository
     */
    public function __construct(SnippetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest  $request
     * @return mixed
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $themeId = $request->input('theme_id');

        $params = $request->validated();
        $theme = Theme::find($themeId);
        $data = $this->repository->getModel()->newQuery()
            ->where('theme','=', $theme->theme_id)
            ->paginate($params['limit'] ?? 100);

        return $this->success(new ItemCollection($data));
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
    public function show(int $id): Detail
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

    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        /** @var Snippet $item */
        $item = $this->repository->find($id);
        $isActive = (bool) $request->get('active');
        $item->is_active = $isActive;
        $item->save();

        return $this->success(new Detail($item));
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
        /** @var Snippet $snippet */
        $snippet = $this->repository->find($id);

        $snippet->delete();

        return $this->success([
            'id' => $id,
        ]);
    }
}
