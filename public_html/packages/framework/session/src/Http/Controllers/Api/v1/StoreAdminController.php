<?php

namespace MetaFox\Session\Http\Controllers\Api\v1;

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
 * | @link \MetaFox\Session\Http\Controllers\Api\StoreAdminController::$controllers.
 */

/**
 * Class StoreAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class StoreAdminController extends ApiController
{
    private DriverRepositoryInterface $drivers;

    /**
     * @param DriverRepositoryInterface $drivers
     */
    public function __construct(DriverRepositoryInterface $drivers)
    {
        $this->drivers = $drivers;
    }

    public function index()
    {
        $data = [];

        $names = $this->drivers->getNamesHasHandlerClass('form-session');

        foreach ($names as $name) {
            $data[] = $this->transformResource($name);
        }

        return $this->success($data);
    }

    private function transformResource(string $name): array
    {
        return [
            'id'       => $name,
            'text'     => __p(sprintf('session::phrase.guide_driver_%s', $name)),
            'can_edit' => true,
        ];
    }

    public function edit(string $name): JsonResponse
    {
        $class = $this->drivers->getDriver('form-session', $name, 'admin');

        $form = new $class();

        return $this->success($form);
    }

    public function update(string $name): JsonResponse
    {
        $class = $this->drivers->getDriver('form-session', $name, 'admin');

        $form = new $class();

        $data = app()->call([$form, 'validated']);

        Settings::save($data);

        Artisan::call('cache:reset');

        $message = __p('core::phrase.updated_successfully');

        $nextAction = [
            'type' => 'navigate/reload',
        ];

        return $this->success($this->transformResource($name), ['nextAction' => $nextAction], $message);
    }
}
