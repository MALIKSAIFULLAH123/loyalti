<?php

namespace MetaFox\Payment\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Payment\Http\Requests\v1\Gateway\ConfigurationRequest;
use MetaFox\Payment\Http\Requests\v1\Gateway\IndexRequest;
use MetaFox\Payment\Http\Resources\v1\Gateway\GatewayConfigurationItemCollection;
use MetaFox\Payment\Http\Resources\v1\Gateway\GatewayItemCollection as ItemCollection;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Payment\Repositories\UserConfigurationRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use Illuminate\Http\Request;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Payment\Http\Controllers\Api\GatewayController::$controllers.
 */

/**
 * Class GatewayController.
 * @codeCoverageIgnore
 * @ignore
 */
class GatewayController extends ApiController
{
    /**
     * @var GatewayRepositoryInterface
     */
    private GatewayRepositoryInterface $repository;

    /**
     * GatewayController Constructor.
     *
     * @param GatewayRepositoryInterface $repository
     */
    public function __construct(GatewayRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $data = $this->repository->getActiveGateways();

        return new ItemCollection($data);
    }

    public function getConfigurations(): JsonResponse
    {
        $configurations = resolve(GatewayRepositoryInterface::class)->getConfigurationGateways();

        return $this->success(new GatewayConfigurationItemCollection($configurations));
    }

    public function getConfigurationForm(string $driver, int $id): JsonResponse
    {
        $class = resolve(DriverRepositoryInterface::class)
            ->getDriver(Constants::DRIVER_TYPE_USER_GATEWAY_FORM, $driver, MetaFox::getResolution());

        $form = resolve($class);

        $parameters = [
            'id' => $id,
        ];

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $parameters);
        }

        return $this->success($form);
    }

    public function updateConfigurations(ConfigurationRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        resolve(UserConfigurationRepositoryInterface::class)->updateConfiguration($id, $data);

        return $this->success([], [], __p('payment::phrase.settings_successfully_updated'));
    }

    /**
     * @throws AuthenticationException
     */
    public function updateMultipleConfigurations(Request $request): JsonResponse
    {
        $data = $request->all();
        $id   = user()->entityId();

        resolve(UserConfigurationRepositoryInterface::class)->updateMultipleConfiguration($id, $data);

        return $this->success([], [], __p('payment::phrase.settings_successfully_updated'));
    }
}
