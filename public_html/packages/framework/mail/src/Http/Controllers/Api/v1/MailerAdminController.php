<?php

namespace MetaFox\Mail\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Mail\Http\Requests\v1\Mailer\Admin\IndexRequest;
use MetaFox\Mail\Http\Requests\v1\Mailer\Admin\StoreRequest;
use MetaFox\Mail\Http\Requests\v1\Mailer\Admin\UpdateRequest;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Core\Http\Controllers\Api\MailerAdminController::$controllers.
 */

/**
 * Class MailerAdminController.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 */
class MailerAdminController extends ApiController
{
    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $mailers = Settings::get('mail.mailers', []);
        $data    = [];

        foreach ($mailers as $id => $config) {
            $data[] = $this->transformMailer($id, $config);
        }

        return $this->success($data);
    }

    public function transformMailer(string $id, ?array $config): array
    {
        $transport = $config['transport'] ?? 'unknown';

        $default        = Settings::get('mail.default');
        $hasFromDrivers = resolve(DriverRepositoryInterface::class)
            ->getNamesHasHandlerClass('form-mailer');
        $canEdit   = in_array($transport, $hasFromDrivers);
        $isDefault = $default != $transport;

        return [
            'id'         => $id,
            'transport'  => $transport,
            'text'       => __p('mail::mailer.' . $transport . '_transport_guide'),
            'is_default' => $isDefault,
            'can_edit'   => $canEdit,
            'links'      => [
                'editItem' => '/mail/mailer/edit/' . $transport . '/' . $id,
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
     * @param  string       $driver
     * @param  string       $name
     * @param  Request      $request
     * @return JsonResponse
     */
    public function edit(string $driver, string $name, Request $request): JsonResponse
    {
        $parameters = [
            'driver' => $driver,
            'name'   => $name,
        ];

        $class = resolve(DriverRepositoryInterface::class)
            ->getDriver('form-mailer', $driver, 'admin');

        $form = new $class($parameters);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $parameters);
        }

        return $this->success($form);
    }

    public function update(UpdateRequest $request, string $name, string $driver): JsonResponse
    {
        $parameters = [
            'name'   => $name,
            'driver' => $driver,
        ];

        $class = resolve(DriverRepositoryInterface::class)
            ->getDriver('form-mailer', $driver, 'admin');

        $form = resolve($class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $parameters);
        }

        $data = app()->call([$form, 'validated'], $parameters);

        $response = Settings::save($data);

        Artisan::call('cache:reset');

        $nextAction = [
            'type'    => 'navigate',
            'payload' => ['url' => '/mail/mailer/browse', 'replace' => true],
        ];

        return $this->success($response, [
            'nextAction' => $nextAction,
        ], __p('core::phrase.save_changed_successfully'));
    }
}
