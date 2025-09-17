<?php

namespace MetaFox\Word\Http\Controllers\Api;

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
 * Class BlockAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class BlockAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\BlockAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
