<?php

namespace MetaFox\Word\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Word\Http\Requests\v1\Block\Admin\IndexRequest;
use MetaFox\Word\Http\Requests\v1\Block\Admin\StoreRequest;
use MetaFox\Word\Http\Requests\v1\Block\Admin\UpdateRequest;
use MetaFox\Word\Http\Resources\v1\Block\Admin\BlockDetail as Detail;
use MetaFox\Word\Http\Resources\v1\Block\Admin\BlockItemCollection as ItemCollection;
use MetaFox\Word\Http\Resources\v1\Block\Admin\BlockItem;
use MetaFox\Word\Http\Resources\v1\Block\Admin\EditBlockForm;
use MetaFox\Word\Http\Resources\v1\Block\Admin\StoreBlockForm;
use MetaFox\Word\Models\Block;
use MetaFox\Word\Repositories\BlockRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Word\Http\Controllers\Api\BlockAdminController::$controllers.
 */

/**
 * Class BlockAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class BlockAdminController extends ApiController
{
    /**
     * @var BlockRepositoryInterface
     */
    private BlockRepositoryInterface $repository;

    /**
     * BlockAdminController Constructor.
     *
     * @param BlockRepositoryInterface $repository
     */
    public function __construct(BlockRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest              $request
     * @return ItemCollection<BlockItem>
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $q      = Arr::get($params, 'q');

        $query = $this->repository->getModel()->newQuery();
        if ($q) {
            $query = $query->addScope(new SearchScope($q, ['word']));
        }
        $data = $query->paginate($params['limit'] ?? 100);

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
        $data   = $this->repository->create($params);

        return $this->success(new Detail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => '/word/block/browse',
                ],
            ],
        ], __p('word::phrase.word_created_successfully'));
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
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->update($params, $id);

        return $this->success(new Detail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => '/word/block/browse',
                ],
            ],
        ], __p('word::phrase.word_updated_successfully'));
    }

    public function create(): JsonResponse
    {
        return $this->success(new StoreBlockForm());
    }

    public function edit(int $id): JsonResponse
    {
        $word = $this->repository->find($id);

        return $this->success(new EditBlockForm($word));
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
        $word = $this->repository->find($id);
        $word->delete();

        return $this->success([
            'id' => $id,
        ]);
    }

    public function batchDestroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id.*' => 'integer|sometimes',
        ]);

        /** @var Block[] $items */
        $items = $this->repository->findWhereIn('id', $data['id'])->all();

        foreach ($items as $item) {
            $item->delete();
        }

        return $this->success([], [], __p('core::phrase.already_saved_changes'));
    }
}
