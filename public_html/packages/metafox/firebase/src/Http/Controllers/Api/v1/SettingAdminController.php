<?php

namespace MetaFox\Firebase\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Firebase\Http\Requests\v1\Admin\StoreRequest;
use MetaFox\Firebase\Http\Resources\v1\Admin\ImportSettingForm;
use MetaFox\Platform\Contracts\SiteSettingRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\PackageManager;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Importer\Http\Controllers\Api\BundleAdminController::$controllers;.
 */

/**
 * Class SettingAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class SettingAdminController extends ApiController
{
    /**
     * @var SiteSettingRepositoryInterface
     */
    private SiteSettingRepositoryInterface $repository;

    /**
     * SiteSettingAdminController constructor.
     *
     * @param SiteSettingRepositoryInterface $repository
     *
     * @ignore
     */
    public function __construct(SiteSettingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $params       = $request->validated();
        $uploadedFile = $params['file'];

        $listener = PackageManager::getListener('metafox/firebase');

        if (!$listener) {
            return $this->error('Firebase app not found');
        }

        $content = file_get_contents($uploadedFile->getRealPath());
        $content = json_decode($content, true);

        $settings = array_keys($listener->getSiteSettings());
        $data     = [];

        foreach ($settings as $setting) {
            if (Arr::get($content, $setting)) {
                Arr::set($data, "firebase.$setting", Arr::get($content, $setting));
            }
        }

        $modifiedSettings = [];
        $this->repository->saveAndCollectModifiedSettings($data, $modifiedSettings);

        Artisan::call('cache:reset');

        app('events')->dispatch('site_settings.updated', 'firebase');

        if (Arr::get($modifiedSettings, 'private')) {
            $this->alert([
                'message' => __p('core::phrase.please_rebuild_your_site'),
            ]);
        }

        $nextAction = ['type' => 'navigate', 'payload' => ['url' => '/firebase/setting']];

        return $this->success([], ['nextAction' => $nextAction]);
    }

    public function create()
    {
        return $this->success(new ImportSettingForm());
    }
}
