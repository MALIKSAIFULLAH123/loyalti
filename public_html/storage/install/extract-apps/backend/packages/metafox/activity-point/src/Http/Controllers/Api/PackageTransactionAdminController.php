<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api;

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
 * Class PackageTransactionAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class PackageTransactionAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\PackageTransactionAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
