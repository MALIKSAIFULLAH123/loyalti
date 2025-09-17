<?php

namespace MetaFox\App\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * --------------------------------------------------------------------------
 * Api Gateway
 * --------------------------------------------------------------------------
 * stub: /packages/controllers/api_gateway.stub.
 *
 * This class solves api versioning problem.
 * DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class StoreAdminController.
 * @ignore
 * @codeCoverageIgnore
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
