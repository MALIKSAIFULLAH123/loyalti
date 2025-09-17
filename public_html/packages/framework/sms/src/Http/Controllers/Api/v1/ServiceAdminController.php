<?php

namespace MetaFox\Sms\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Sms\Http\Requests\v1\Service\Admin\IndexRequest;
use MetaFox\Sms\Http\Requests\v1\Service\Admin\StoreRequest;
use MetaFox\Sms\Http\Requests\v1\Service\Admin\UpdateRequest;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Core\Http\Controllers\Api\ServiceAdminController::$controllers.
 */

/**
 * Class ServiceAdminController.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 */
class ServiceAdminController extends ApiController
{
    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return JsonResponse
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $services = Settings::get('sms.services', []);
        $data     = [];

        foreach ($services as $id => $config) {
            $service = $this->transformService($id, $config ?? []);

            if (empty($service)) {
                continue;
            }

            $data[] = $service;
        }

        return $this->success($data);
    }

    /**
     * transformService.
     *
     * @param  string       $id
     * @param  array<mixed> $config
     * @return array<mixed>
     */
    public function transformService(string $id, array $config = []): array
    {
        $service = Arr::get($config, 'service');
        if (empty($service)) {
            return [];
        }

        $default        = Settings::get('sms.default');
        $hasFromDrivers = resolve(DriverRepositoryInterface::class)
            ->getNamesHasHandlerClass('sms-service-form');
        $canEdit   = in_array($service, $hasFromDrivers);
        $isDefault = $default != $service;
        $canDelete = $canEdit && !$isDefault;
        $isCore    = Arr::get($config, 'is_core') ?? true;

        $text = __p("sms::service.{$service}_service_guide");
        if (!$isCore) {
            $text = __p($service . "::service.{$service}_service_guide");
        }

        return [
            'id'         => $id,
            'service'    => $service,
            'text'       => $text,
            'is_default' => $isDefault,
            'can_edit'   => $canEdit,
            'can_delete' => $canDelete,
            'links'      => [
                'editItem' => '/sms/service/edit/' . $service,
            ],
        ];
    }

    /**
     * @param  StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        return $this->success([]);
    }

    /**
     * @param  string       $service
     * @param  Request      $request
     * @return JsonResponse
     */
    public function edit(string $service, Request $request): JsonResponse
    {
        $class = resolve(DriverRepositoryInterface::class)
            ->getDriver('sms-service-form', $service, 'admin');

        $form = resolve($class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], array_merge($request->route()->parameters(), compact('service')));
        }

        return $this->success($form);
    }

    public function update(UpdateRequest $request, string $service): JsonResponse
    {
        $class = resolve(DriverRepositoryInterface::class)
            ->getDriver('sms-service-form', $service, 'admin');

        $form = resolve($class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], [$service]);
        }

        $data = app()->call([$form, 'validated'], [$service]);

        $response = Settings::save($data);

        Artisan::call('cache:reset');

        $nextAction = [
            'type'    => 'navigate',
            'payload' => ['url' => '/sms/service/browse', 'replace' => true],
        ];

        return $this->success($response, [
            'nextAction' => $nextAction,
        ], __p('core::phrase.save_changed_successfully'));
    }
}
