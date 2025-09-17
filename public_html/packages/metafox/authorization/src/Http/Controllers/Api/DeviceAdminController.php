<?php

namespace MetaFox\Authorization\Http\Controllers\Api;

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
 * Class DeviceAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class DeviceAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\DeviceAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
