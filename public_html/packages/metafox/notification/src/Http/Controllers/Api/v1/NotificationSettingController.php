<?php

namespace MetaFox\Notification\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Notification\Contracts\ChannelManagerInterface;
use MetaFox\Notification\Http\Requests\v1\NotificationSetting\UpdateRequest;
use MetaFox\Notification\Http\Resources\v1\NotificationSetting\NotificationSettingForm;
use MetaFox\Notification\Http\Resources\v1\NotificationSetting\NotificationSettingMobileForm;
use MetaFox\Notification\Repositories\SettingRepositoryInterface;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Notification\Http\Controllers\Api\NotificationSettingController::$controllers;
 */

/**
 * Class NotificationSettingController
 *
 * @codeCoverageIgnore
 * @ignore
 */
class NotificationSettingController extends ApiController
{
    /**
     * NotificationSettingController Constructor
     *
     * @param SettingRepositoryInterface $repository
     * @param ChannelManagerInterface    $channelManager
     */
    public function __construct(
        protected SettingRepositoryInterface $repository,
        protected ChannelManagerInterface    $channelManager
    ) {}

    /**
     * Update item
     *
     * @param UpdateRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function update(Request $request): JsonResponse
    {
        $params  = $request->all();
        $context = user();

        $form = match (MetaFox::getResolution()) {
            MetaFoxConstant::RESOLUTION_MOBILE => new NotificationSettingMobileForm(),
            default                            => new NotificationSettingForm()
        };

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        if (method_exists($form, 'validated')) {
            $params = app()->call([$form, 'validated'], $request->route()->parameters());
        }

        $result = $this->repository->updateByChannel($context, $params);

        if (!$result) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'));
        }

        if ($context instanceof IsNotifiable) {
            $this->channelManager->forgetChannelCacheForNotifiable($context);
        }

        return $this->success([], [], __p('core::phrase.updated_successfully'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @deprecated v5.2
     */
    public function getSettingForm(Request $request): JsonResponse
    {
        $channel = $request->get('channel');
        $class   = resolve(DriverRepositoryInterface::class)
            ->getDriver('notification-channel-form', $channel, MetaFox::getResolution());

        $form = resolve($class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form);
    }
}
