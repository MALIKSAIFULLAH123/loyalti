<?php

namespace MetaFox\Profile\Http\Controllers\Api;

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
 * class ProfileAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class ProfileAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\ProfileAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
