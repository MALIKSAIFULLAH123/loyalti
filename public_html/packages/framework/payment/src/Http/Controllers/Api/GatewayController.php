<?php

namespace MetaFox\Payment\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController as ApiGatewayController;

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
 * Class GatewayController.
 * @codeCoverageIgnore
 * @ignore
 */
class GatewayController extends ApiGatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\GatewayController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
