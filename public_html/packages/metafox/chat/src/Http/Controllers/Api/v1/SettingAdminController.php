<?php

namespace MetaFox\Chat\Http\Controllers\Api\v1;

use MetaFox\Chat\Repositories\SubscriptionRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

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
    public function __construct(public SubscriptionRepositoryInterface $subscriptionRepository)
    {
    }

    public function migrateToChatPlus()
    {
        $context = user();

        $this->subscriptionRepository->migrateToChatPlus($context);

        $nextAction = ['type' => 'navigate', 'payload' => ['url' => '/chat/setting']];

        return $this->success([], ['nextAction' => $nextAction]);
    }
}
