<?php

namespace MetaFox\Mobile\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Mobile\Http\Controllers\Api\SmartBannerAdminController::$controllers;.
 */

/**
 * Class SmartBannerAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class SmartBannerAdminController extends ApiController
{
    public function __construct(protected DriverRepositoryInterface $driverRepository)
    {
    }

    public function updateSettings(string $driver): JsonResponse
    {
        $class = $this->driverRepository->getDriver('form-settings', $driver, 'admin');

        $form = resolve($class);

        $parameters = [
            'driver' => $driver,
        ];

        $data = app()->call([$form, 'validated'], $parameters);

        $response = Settings::save($data);

        Artisan::call('cache:reset');

        return $this->success($response, [], __p('core::phrase.save_changed_successfully'));
    }
}
