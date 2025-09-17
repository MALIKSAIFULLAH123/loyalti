<?php

namespace MetaFox\SEO\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\SEO\Http\Requests\v1\Meta\Admin\IndexRequest;
use MetaFox\SEO\Http\Requests\v1\Meta\Admin\StoreRequest;
use MetaFox\SEO\Http\Requests\v1\Meta\Admin\UpdateRequest;
use MetaFox\SEO\Http\Requests\v1\Meta\Admin\UpdateSchemaRequest;
use MetaFox\SEO\Http\Resources\v1\Meta\Admin\MetaItem as Detail;
use MetaFox\SEO\Http\Resources\v1\Meta\Admin\MetaItemCollection as ItemCollection;
use MetaFox\SEO\Http\Resources\v1\Meta\Admin\StoreMetaForm;
use MetaFox\SEO\Http\Resources\v1\Meta\Admin\UpdateMetaForm;
use MetaFox\SEO\Models\Meta;
use MetaFox\SEO\Models\Schema;
use MetaFox\SEO\Repositories\MetaRepositoryInterface;
use MetaFox\SEO\Repositories\SchemaRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Core\Http\Controllers\Api\MetaAdminController::$controllers.
 */

/**
 * Class MetaAdminController.
 * @codeCoverageIgnore
 * @method Meta find($id, $columns = ['*'])
 * @ignore
 */
class MetaAdminController extends ApiController
{
    /**
     * MetaAdminController Constructor.
     *
     * @param MetaRepositoryInterface   $repository
     * @param PhraseRepositoryInterface $phraseRepository
     * @param SchemaRepositoryInterface $schemaRepository
     */
    public function __construct(
        protected MetaRepositoryInterface   $repository,
        protected PhraseRepositoryInterface $phraseRepository,
        protected SchemaRepositoryInterface $schemaRepository
    ) {}

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params     = $request->validated();
        $search     = Arr::get($params, 'q');
        $packageId  = Arr::get($params, 'package_id');
        $resolution = Arr::get($params, 'resolution', 'web');

        $table = $this->repository->getModel()->getTable();
        $query = $this->repository->getModel()->newQuery()->select(["$table.*"]);

        if ($search) {
            $searchScope = new SearchScope($search, [$table . '.url']);
            $searchScope->setTable($table);
            $query = $query->addScope($searchScope);
        }

        if ($resolution) {
            $query->where([$table . '.resolution' => $resolution]);
        }

        if ($packageId) {
            $query->where($table . '.package_id', $packageId);
        }

        $packageScope = new PackageScope($table);

        $query = $query->addScope($packageScope);

        $data = $query
            ->where($table . '.custom_sharing_route', 0)
            ->whereNotNull($table . '.url')
            ->paginate($params['limit'] ?? 100);

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
     * @param UpdateRequest $request
     * @param int           $id
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        /** @var Meta $model */
        $model = $this->repository->find($id);

        $phraseUpdates = [];
        $phraseCreates = [];

        $map = [
            'phrase_title'       => 'title',
            'phrase_heading'     => 'heading',
            'phrase_description' => 'description',
            'phrase_keywords'    => 'keywords',
        ];

        $phraseCollections = $this->getPhrasesExists($model, array_keys($map));

        foreach ($map as $key => $name) {
            $attributeData = Arr::get($params, $name);

            if (!is_array($attributeData)) {
                continue;
            }

            foreach ($attributeData as $locale => $text) {
                if ($model->{$key}) {
                    $phraseUpdates[] = [$model->{$key}, $text, $locale];
                    continue;
                }

                $name         = sprintf('%s_%s_%s', $model->entityType(), $model->entityId(), $key);
                $phraseKey    = toTranslationKey($model->module_id, 'seo', $name);
                $params[$key] = $phraseKey;

                if ($phraseCollections->where('key', $phraseKey)->where('locale', $locale)->isNotEmpty()) {
                    $phraseUpdates[] = [$phraseKey, $text, $locale];
                    continue;
                }

                $phraseCreates[] = [
                    'name'         => $name,
                    'locale'       => $locale,
                    'text'         => $text,
                    'default_text' => $text,
                    'is_modified'  => 1,
                    'package_id'   => $model->package_id,
                    'namespace'    => $model->module_id,
                    'group'        => 'seo',
                ];
            }
        }

        $model->update($params);

        if ($model->wasChanged('robots_no_index')) {
            $urls = $this->repository->getModel()
                ->newModelQuery()
                ->whereNotNull('url')
                ->where('robots_no_index', 1)
                ->get()
                ->collect()
                ->pluck('robots_no_index', 'url')
                ->toArray();

            Settings::updateSetting(
                'seo',
                'seo.sitemap_no_indexes_urls',
                null,
                null,
                $urls,
                'array',
                false,
                true,
            );
        }

        if (!empty($phraseCreates)) {
            app('events')->dispatch('localize.phrase.mass_create', [$phraseCreates], true);
        }

        Log::channel('dev')->info('update phrases', $phraseUpdates);

        app('events')->dispatch('localize.phrase.mass_update', [$phraseUpdates], true);

        Artisan::call('cache:reset');

        return $this->success(new Detail($model));
    }

    protected function getPhrasesExists($model, $map): Collection
    {
        $map = array_map(function ($item) use ($model) {
            $name = sprintf('%s_%s_%s', $model->entityType(), $model->entityId(), $item);
            return toTranslationKey($model->module_id, 'seo', $name);
        }, $map);

        return $this->phraseRepository->getModel()->newQuery()
            ->whereIn('key', array_values($map))->get();
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

    /**
     * Get the creation form.
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        return $this->success(new StoreMetaForm());
    }

    /**
     * Get updating form.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        $resource = $this->repository->find($id);

        return $this->success(new UpdateMetaForm($resource));
    }

    /**
     * Get updating form.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function translate(Request $request): JsonResponse
    {
        $url  = $request->get('url');
        $path = 'sharing/' . trim($url, '/');

        defined('MFOX_SHARING_RETRY_ARRAY') or define('MFOX_SHARING_RETRY_ARRAY', true);

        $response = Route::dispatch(Request::create($path, 'GET', []));

        $result = json_decode($response->getContent(), true);

        $name = Arr::get($result, 'data.meta:name');

        if (!$name) {
            $name = normalize_seo_meta_name($url);
        }

        $resource = $this->repository->getModel()
            ->newQuery()
            ->where('name', '=', $name)
            ->first();

        if (!$resource) {
            $resource = $this->repository->createSampleMeta($name);
        }

        return $this->success(new UpdateMetaForm($resource));
    }

    public function updateSchema(UpdateSchemaRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        /** @var Meta $model */
        $model = $this->repository->find($id);

        $schema = $model->schema;
        if ($schema instanceof Schema) {
            $schema->update($params);
            return $this->success(new Detail($model));
        }

        $data = [
            'meta_id'        => $id,
            'is_modified'    => true,
            'schema'         => Arr::get($params, 'schema', []),
            'schema_default' => Arr::get($params, 'schema', $this->schemaRepository->getStructuredDefault($model)),
        ];

        $schemaModel = new Schema();
        $schemaModel->fill($data);
        $schemaModel->save();

        return $this->success(new Detail($model));
    }
}
