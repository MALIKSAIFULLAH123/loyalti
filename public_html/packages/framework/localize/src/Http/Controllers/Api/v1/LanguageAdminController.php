<?php

namespace MetaFox\Localize\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use MetaFox\App\Http\Resources\v1\Package\Admin\PackageDetail;
use MetaFox\Localize\Http\Requests\v1\Language\Admin\IndexRequest;
use MetaFox\Localize\Http\Requests\v1\Language\Admin\MakeLanguageRequest;
use MetaFox\Localize\Http\Requests\v1\Language\Admin\UpdateRequest;
use MetaFox\Localize\Http\Requests\v1\Language\Admin\UploadCSVRequest;
use MetaFox\Localize\Http\Resources\v1\Language\Admin\LanguageDetail as Detail;
use MetaFox\Localize\Http\Resources\v1\Language\Admin\LanguageItem;
use MetaFox\Localize\Http\Resources\v1\Language\Admin\LanguageItemCollection;
use MetaFox\Localize\Http\Resources\v1\Language\Admin\MakeLanguageForm;
use MetaFox\Localize\Http\Resources\v1\Language\Admin\UpdateLanguageForm;
use MetaFox\Localize\Http\Resources\v1\Language\Admin\UploadCSVForm;
use MetaFox\Localize\Models\Language;
use MetaFox\Localize\Repositories\LanguageRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\Platform\PackageManager;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Localize\Http\Controllers\Api\LanguageAdminController::$controllers.
 */

/**
 * Class LanguageAdminController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @group admin/language
 * @authenticated
 */
class LanguageAdminController extends ApiController
{
    /**
     * @var LanguageRepositoryInterface
     */
    private LanguageRepositoryInterface $repository;

    /**
     * LanguageAdminController constructor.
     *
     * @param LanguageRepositoryInterface $repository
     *
     * @group admin/language
     * @ignore
     */
    public function __construct(LanguageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse languages.
     *
     * @param IndexRequest $request
     *
     * @return LanguageItemCollection<LanguageItem>
     */
    public function index(IndexRequest $request): LanguageItemCollection
    {
        $params = $request->validated();

        $data = $this->repository->paginate($params['limit'] ?? 100);

        return new LanguageItemCollection($data);
    }

    /**
     * View language.
     *
     * @param int $id
     *
     * @return Detail
     * @group admin/language
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update language.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @group admin/language
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->update($params, $id);

        return $this->success(new Detail($data), [], __p('localize::admin.language_successfully_updated'));
    }

    public function exportPhrases(int $id)
    {
        /** @var Language $model */
        $model = $this->repository->find($id);

        $filename = tempnam(sys_get_temp_dir(), 'languages');

        resolve('translation')
            ->exportTranslationsCSV($filename, $model->language_code);

        $headers = ['Access-Control-Expose-Headers' => 'Content-Disposition'];

        return response()->download($filename, "$model->language_code.csv", $headers)
            ->deleteFileAfterSend(true);
    }

    /**
     * Update active status.
     *
     * @param ActiveRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws ValidationException|AuthenticationException
     * @group admin/currency
     * @ignore
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $context = user();

        $data = $request->validated();

        $isActive = (bool) Arr::get($data, 'active', true);

        if (!$this->repository->updateActive($context, $id, $isActive)) {
            return $this->error(__p('localize::admin.can_not_action_on_default_currency'));
        }

        Artisan::call('cache:reset');

        $message = match ($isActive) {
            true  => __p('localize::admin.language_successfully_activated'),
            false => __p('localize::admin.language_successfully_deactivated')
        };

        return $this->success([
            'id'        => $id,
            'is_active' => $isActive,
        ], [], $message);
    }

    /**
     * Delete language.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group admin/language
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteLanguage(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('localize::phrase.language_deleted_successfully'));
    }

    public function create()
    {
        return $this->success(new MakeLanguageForm());
    }

    public function edit(Request $request): JsonResponse
    {
        $form = resolve(UpdateLanguageForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form);
    }

    public function store(MakeLanguageRequest $request)
    {
        $params = $request->validated();

        $package              = sprintf('%s/lang-%s', strtolower($params['--vendor']), strtolower($params['--language_code']));
        $params['--name']     = Str::studly('lang-' . $params['--language_code']);
        $params['package']    = $package;
        $params['--author']   = config('app.site_email');
        $params['--homepage'] = config('app.url');

        Artisan::call('package:make-language', $params);

        $model = resolve('core.packages')->setupPackage($package);

        return $this->success(new PackageDetail($model), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url'     => '/localize/language/browse',
                    'replace' => true,
                ],
            ],
        ], __p('localize::phrase.create_language_successfully'));
    }

    public function uploadCSV($id)
    {
        $model = $this->repository->find($id);

        return $this->success(new UploadCSVForm($model));
    }

    public function uploadCSVFile($id, UploadCSVRequest $request): JsonResponse
    {
        /** @var UploadedFile $moduleFile */
        $phraseFile = $request->file('file');

        /** @var Language $language */
        $language    = $this->repository->find($id);
        $destination = null;
        $packageId   = $language->package_id;

        if ($packageId) {
            $destination = base_path(sprintf(
                '%s/resources/lang/%s.csv',
                PackageManager::getPath($packageId),
                $language->language_code
            ));
        }

        try {
            resolve('translation')
                ->importTranslationsFromCSV($phraseFile->getRealPath());

            if ($destination) {
                copy($phraseFile->getRealPath(), $destination);
                Log::channel('dev')->debug("Copy phrase to $destination");
            }

            Artisan::call('optimize:clear');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), $exception->getCode() ?: 400);
        }

        $this->navigate($language->admin_browse_url);

        return $this->success([], [], __p('localize::phrase.phrase_imported_successfully'));
    }

    public function missingPhrases(int $id): JsonResponse
    {
        $language = $this->repository->addMissPhrases($id);

        $this->navigate($language->admin_browse_url);

        return $this->success(new Detail($language), [], __p('localize::phrase.missing_phrases_added_successfully'));
    }
}
