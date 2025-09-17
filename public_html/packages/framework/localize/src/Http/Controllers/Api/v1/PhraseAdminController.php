<?php

namespace MetaFox\Localize\Http\Controllers\Api\v1;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use MetaFox\Localize\Http\Requests\v1\Phrase\Admin\ImportRequest;
use MetaFox\Localize\Http\Requests\v1\Phrase\Admin\IndexRequest;
use MetaFox\Localize\Http\Requests\v1\Phrase\Admin\StoreRequest;
use MetaFox\Localize\Http\Requests\v1\Phrase\Admin\UpdateRequest;
use MetaFox\Localize\Http\Resources\v1\Phrase\Admin\PhraseDetail as Detail;
use MetaFox\Localize\Http\Resources\v1\Phrase\Admin\PhraseItem;
use MetaFox\Localize\Http\Resources\v1\Phrase\Admin\StorePhraseForm;
use MetaFox\Localize\Http\Resources\v1\Phrase\Admin\UpdatePhraseForm;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Localize\Support\PackageTranslationExporter;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\PackageManager;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Localize\Http\Controllers\Api\PhraseAdminController::$controllers.
 */

/**
 * Class PhraseAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group admin/phrase
 */
class PhraseAdminController extends ApiController
{
    /**
     * @var PhraseRepositoryInterface
     */
    private PhraseRepositoryInterface $repository;

    /**
     * PhraseAdminController constructor.
     *
     * @param PhraseRepositoryInterface $repository
     */
    public function __construct(PhraseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse phrases.
     *
     * @param IndexRequest $request
     *
     * @return ResourceCollection<PhraseItem>
     *
     * @group admin/phrase
     */
    public function index(IndexRequest $request): ResourceCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewPhrases($params);

        return PhraseItem::collection($data);
    }

    /**
     * @param  IndexRequest                   $request
     * @return ResourceCollection<PhraseItem>
     */
    public function missing(IndexRequest $request): ResourceCollection
    {
        // This route will not be public in production.
        if (config('app.env') !== 'local') {
            return PhraseItem::collection([]);
        }

        $params = $request->validated();

        /** @var Collection<PhraseItem> $query */
        $data = $this->repository->getModel()
            ->newQuery()
            ->where(function ($builder) {
                $builder->where('text', 'like', '%::%.%');
            });

        if (($group = $params['group'] ?? null)) {
            $data->where('group', '=', $group);
        }

        if (($locale = $params['locale'] ?? null)) {
            $data->where('locale', '=', $locale);
        }

        if (($package = $params['package_id'] ?? null)) {
            $data->where('package_id', '=', $package);
        }

        if (($q = $params['q'] ?? null)) {
            $data->where('text', 'like', '%' . $q . '%');
        }

        $customExcludeKeys = [
            'menu::phrase.menu_item_label_desc',
        ];

        $data->whereNotIn('key', $customExcludeKeys);

        $data = $data->paginate();

        return PhraseItem::collection($data);
    }

    /**
     * Export.
     *
     * Export phrases to filesystem
     *
     * @return JsonResponse
     */
    public function exportPhraseToFilesystem(): JsonResponse
    {
        $exporter = resolve(PackageTranslationExporter::class);

        foreach (PackageManager::getPackageNames() as $packageName) {
            $exporter->exportTranslations($packageName);
        }

        return $this->success([], [], 'Update all phrases resource files');
    }

    /**
     * Create a new phrase.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidationException
     * @group admin/phrase
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $phraseData = $request->validated();

        foreach ($phraseData as $params) {
            $phrase = $this->repository->createPhrase($params);
        }

        $this->navigate($phrase->admin_browse_url, true);

        Artisan::call('cache:reset');

        return $this->success([], [], __p('localize::phrase.phrase_created_successfully'));
    }

    /**
     * View phrase.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group admin/phrase
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->find($id);

        return $this->success(new Detail($data));
    }

    /**
     * Update phrase.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws ValidationException
     * @group admin/phrase
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updatePhrase($id, $params);

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('localize::phrase.phrase_updated_successfully'));
    }

    /**
     * Delete phrase.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group admin/phrase
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->delete($id);

        Artisan::call('cache:reset');

        return $this->success([
            'id' => $id,
        ], [], __p('localize::phrase.phrase_deleted_successfully'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $id = $request->get('id');

        $this->repository->getModel()
            ->newQuery()
            ->whereIn('id', is_array($id) ? $id : [$id])
            ->delete();

        return $this->success($id);
    }

    public function translate(Request $request): JsonResponse
    {
        $key  = $request->get('translation_key');
        $text = $request->get('translation_text') ?? '';

        $this->repository->addTranslation($key, $text, 'en');

        [$namespace] = app('translator')->parseKey($key);

        $package = PackageManager::getByAlias($namespace);

        if (!$package) {
            $package = 'metafox/core';
        }

        // check translation
        if (config('app.env') === 'local') {
            resolve(PackageTranslationExporter::class)->exportTranslations($package);
        }

        Artisan::call('cache:reset');

        return $this->success([], [], __p('core::phrase.save_changes'));
    }

    public function create(): JsonResponse
    {
        return $this->success(new StorePhraseForm());
    }

    public function edit(int $id): JsonResponse
    {
        $model = $this->repository->find($id);

        return $this->success(new UpdatePhraseForm($model));
    }

    public function suggest(Request $request): JsonResponse
    {
        $q = $request->get('q');

        /** @var Collection<Phrase> $phrases */
        $phrases = $this->repository->viewPhrases([
            'q'     => $q,
            'limit' => 20,
            'page'  => 1,
        ]);

        $data = [];
        foreach ($phrases as $phrase) {
            $data[] = [
                'value'   => $phrase->key,
                'label'   => $phrase->key,
                'caption' => $phrase->text ? $phrase->text : 'empty',
            ];
        }

        return $this->success($data, ['q' => $q]);
    }

    public function import(ImportRequest $request): JsonResponse
    {
        /** @var UploadedFile $moduleFile */
        $phraseFile = $request->file('file');

        try {
            resolve('translation')
                ->importTranslationsFromCSV($phraseFile->getRealPath());

            Artisan::call('cache:reset');
        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), '402');
        }

        return $this->success([], [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url'     => '/localize/phrase/browse',
                    'replace' => false,
                ],
            ],
        ], __p('localize::phrase.phrase_imported_successfully'));
    }
}
