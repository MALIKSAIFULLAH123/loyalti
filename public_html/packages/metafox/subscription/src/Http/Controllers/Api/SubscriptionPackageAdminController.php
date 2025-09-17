<?php

namespace MetaFox\Subscription\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * --------------------------------------------------------------------------
 * Api Gateway
 * --------------------------------------------------------------------------
 * stub: /packages/controllers/api_gateway.stub.
 *
 * This class solve api versioning problem.
 * DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class SubscriptionPackageAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class SubscriptionPackageAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\SubscriptionPackageAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
