<?php

namespace MetaFox\InAppPurchase\Http\Controllers\Api;

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
 * Class GoogleServiceAccountAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class GoogleServiceAccountAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\GoogleServiceAccountAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
