<?php

namespace MetaFox\Core\Http\Controllers\Api\v1;

use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Core\Constants;
use MetaFox\Core\Http\Requests\v1\SiteSetting\Admin\StoreRequest;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Core\Repositories\Eloquent\SiteSettingRepository;
use MetaFox\Platform\Contracts\SiteSettingRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Core\Http\Controllers\Api\SiteSettingAdminController::$controllers.
 */

/**
 * Class SiteSettingAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @group admin/settings
 * @authenticated
 */
class SiteSettingAdminController extends ApiController
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

    /**
     * View setting form.
     *
     * @param string      $package
     * @param string|null $type
     *
     * @return JsonResponse
     */
    public function getSiteSettingForm(string $package, ?string $type = null): JsonResponse
    {
        $name = $type ? "$package.$type" : $package;

        if (!in_array($package, app('core.packages')->getActivePackageAliases())) {
            throw new RecordsNotFoundException();
        }

        $class = resolve(DriverRepositoryInterface::class)
            ->getDriver(Constants::DRIVER_TYPE_FORM_SETTINGS, $name, 'admin');

        $driver = new $class();

        if (method_exists($driver, 'boot')) {
            app()->call([$driver, 'boot'], request()->route()->parameters());
        }

        return $this->success($driver);
    }

    /**
     * Update setting.
     *
     * @param  Request      $request
     * @param  string       $package
     * @param  string|null  $type
     * @return JsonResponse
     * @group admin/setting
     */
    public function store(Request $request, string $package, ?string $type = null): JsonResponse
    {
        $name = $type ? "$package.$type" : $package;

        $data = $request->all();

        $class = resolve(DriverRepositoryInterface::class)
            ->getDriver(Constants::DRIVER_TYPE_FORM_SETTINGS, $name, 'admin');

        $form = new $class();

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        if (method_exists($form, 'validated')) {
            $data = app()->call([$form, 'validated'], $request->route()->parameters());
        }

        $modified = [];
        $response        = $this->repository->saveAndCollectModifiedSettings($data, $modified);

        app('events')->dispatch('site_settings.updated', $package);

        $alertMessage = null;
        $redirectUrl = null;
        $successMessage = __p('core::phrase.save_changed_successfully');

        /* @todo should remove this and only display messages for settings that are actually used in the client */
        if (Arr::get($modified, 'private')) {
            $alertMessage = __p('core::phrase.please_rebuild_your_site');
        }

        // control the response behaviors
        if (method_exists($form, 'redirectUrl')) {
            $redirectUrl = $form->redirectUrl();
        }

        if (method_exists($form, 'successMessage')) {
            $successMessage = $form->successMessage($modified);
        }

        if (method_exists($form, 'alertMessage')) {
            $alertMessage = $form->alertMessage($modified);
        }

        Artisan::call('cache:reset');

        if (!empty($redirectUrl)) {
            $this->navigate($redirectUrl);
        }

        if (!empty($alertMessage)) {
            $this->alert($alertMessage);
        }

        return $this->success($response, [], $successMessage);
    }

    /**
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
