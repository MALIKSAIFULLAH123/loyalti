<?php

namespace MetaFox\Mobile\Http\Controllers\Api;

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
 * Class AdMobConfigAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class AdMobConfigAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\AdMobConfigAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
