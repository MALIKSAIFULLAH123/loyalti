<?php

namespace MetaFox\Session\Http\Controllers\Api;

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
 * Class StoreAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class StoreAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\StoreAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
