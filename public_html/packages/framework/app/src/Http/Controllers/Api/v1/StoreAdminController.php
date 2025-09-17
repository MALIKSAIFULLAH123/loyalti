<?php

namespace MetaFox\App\Http\Controllers\Api\v1;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MetaFox\App\Http\Requests\v1\AppStoreProduct\Admin\IndexRequest;
use MetaFox\App\Http\Requests\v1\AppStoreProduct\Admin\SearchFormRequest;
use MetaFox\App\Http\Resources\v1\AppStoreProduct\Admin\SearchForm;
use MetaFox\App\Http\Resources\v1\AppStoreProduct\Admin\StoreAppDetail;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\App\Support\MetaFoxStore;
use MetaFox\App\Support\PackageInstaller;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

class StoreAdminController extends ApiController
{
    /**
     * @var MetaFoxStore
     */
    private MetaFoxStore $store;

    private PackageRepositoryInterface $packageRepository;

    public function __construct(PackageRepositoryInterface $packageRepository)
    {
        $this->packageRepository = $packageRepository;

        $this->store = resolve(MetaFoxStore::class);
    }

    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $limit = Arr::get($params, 'limit');
        if ($limit) {
            $params['per_page'] = (int) $limit;
            Arr::forget($params, 'limit');
        }

        $products = Cache::remember(
            'STORE_' . md5(implode('', $params)),
            10,
            function () use ($params) {
                return $this->store->browse($params);
            }
        );

        return $this->success($products);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->store->show($id);

        return $this->success(new StoreAppDetail($data));
    }

    /**
     * View search form.
     *
     * @param SearchFormRequest $request
     *
     * @return JsonResponse
     */
    public function form(SearchFormRequest $request): JsonResponse
    {
        $data = $this->store->getSearchFormData();

        return $this->success(new SearchForm($data));
    }

    public function install(Request $request): JsonResponse
    {
        $storeAppId      = $request->get('app_id');
        $name            = $request->get('name');
        $app_version     = $request->get('app_version');
        $release_channel = $request->get('release_channel');

        try {
            $this->packageRepository->setInstallationStatus($name, 'downloading');

            $filename = $this->store->downloadProduct($name, $app_version, $release_channel);

            $this->packageRepository->setInstallationStatus($name, 'installing');

            resolve(PackageInstaller::class)->install($filename);

            $this->packageRepository->setInstallationStatus($name, '');

            Artisan::call('optimize:clear');
        } catch (Exception $exception) {
            Log::channel('installation')->debug('\MetaFox\App\Http\Controllers\Api\v1\StoreAdminController::install failed ' . $exception->getMessage() . $exception->getTraceAsString());

            $this->packageRepository->setInstallationStatus($name, '');

            return $this->error($exception->getMessage(), 402);
        }

        $data = $this->store->show($storeAppId);

        return $this->success($data, [
            'time_out_message' => 'manual',
        ], __p('core::phrase.installed_successfully'));
    }

    public function latest(Request $request, string $type = 'app'): JsonResponse
    {
        $rules     = [
            'limit' => ['sometimes', 'numeric'],
            'page'  => ['sometimes', 'numeric', 'min:1'],
        ];
        $validator = Validator::make($request->all(), $rules);

        $params = $validator->validated();
        $params = array_merge($params, [
            'type'     => $type,
            'sort'     => 'latest',
            'per_page' => Arr::get($params, 'limit', 4),
        ]);

        $data = $this->store->latest($params);

        return $this->success($data);
    }
}
