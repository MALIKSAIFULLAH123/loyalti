<?php

namespace MetaFox\Payment\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController as ApiGatewayController;

/**
 * --------------------------------------------------------------------------
 *  Api Gateway
 * --------------------------------------------------------------------------.
 *
 * This class solves api versioning problem.
 * DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class GatewayAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class GatewayAdminController extends ApiGatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\GatewayAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
