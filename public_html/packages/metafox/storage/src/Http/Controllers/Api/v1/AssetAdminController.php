<?php

namespace MetaFox\Storage\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Storage\Http\Requests\v1\Asset\Admin\IndexRequest;
use MetaFox\Storage\Http\Requests\v1\Asset\Admin\RevertRequest;
use MetaFox\Storage\Http\Requests\v1\Asset\Admin\StoreRequest;
use MetaFox\Storage\Http\Requests\v1\Asset\Admin\UpdateRequest;
use MetaFox\Storage\Http\Resources\v1\Asset\Admin\AssetItem;
use MetaFox\Storage\Http\Resources\v1\Asset\Admin\AssetItem as Detail;
use MetaFox\Storage\Http\Resources\v1\Asset\Admin\AssetItemCollection as ItemCollection;
use MetaFox\Storage\Http\Resources\v1\Asset\Admin\EditAssetForm;
use MetaFox\Storage\Http\Resources\v1\Asset\Admin\RevertAssetForm;
use MetaFox\Storage\Models\Asset;
use MetaFox\Storage\Repositories\AssetRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Storage\Http\Controllers\Api\AssetAdminController::$controllers.
 */

/**
 * Class AssetAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class AssetAdminController extends ApiController
{
    /**
     * @var AssetRepositoryInterface
     */
    private AssetRepositoryInterface $repository;

    /**
     * AssetAdminController Constructor.
     *
     * @param AssetRepositoryInterface $repository
     */
    public function __construct(AssetRepositoryInterface $repository)
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

        $search   = Arr::get($params, 'q');
        $limit    = Arr::get($params, 'limit');
        $moduleId = Arr::get($params, 'module_id');

        $query = $this->repository->getModel()->newModelQuery()->select('storage_assets.*');

        $query->join('packages', function ($joinClause) {
            $joinClause->on('packages.name', '=', 'storage_assets.package_id');
        });

        if ($search) {
            $searchScope = new SearchScope($search, ['storage_assets.name']);
            $query       = $query->addScope($searchScope);
        }

        if ($moduleId) {
            $query->where('storage_assets.module_id', $moduleId);
        }

        return new ItemCollection($query->orderBy('storage_assets.name')->paginate($limit));
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return
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

    public function edit(int $id): JsonResponse
    {
        $asset = $this->repository->find($id);

        return $this->success(new EditAssetForm($asset));
    }

    public function upload(int $id, Request $request): JsonResponse
    {
        /** @var Asset $asset */
        $asset = $this->repository->find($id);

        $file = $request->file('file', null);

        if (!$file) {
            return $this->success(new AssetItem($asset));
        }

        $name = app('storage.path')->fileName($file->extension());

        $storageFile = app('storage')->putFileAs('asset', 'asset', $file, $name);

        $asset->file_id = $storageFile->id;

        $asset->save();

        $asset->refresh();

        app('events')->dispatch('storage.asset.uploaded', [$asset]);

        return $this->success(new AssetItem($asset), [], __p('storage::phrase.asset_uploaded_successfully'));
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

    public function revertForm(Request $request, int $id): JsonResponse
    {
        $form = resolve(RevertAssetForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], array_merge($request->route()->parameters(), ['id' => $id]));
        }

        return $this->success($form);
    }

    public function revert(RevertRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $asset = $this->repository->restoreDefaultAsset($id, $params);

        app('events')->dispatch('storage.asset.reverted', [$asset]);

        return $this->success(new Detail($asset), [], __p('storage::phrase.asset_reset_successfully'));
    }
}
