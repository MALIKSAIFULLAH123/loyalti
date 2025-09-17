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
 * class StructureAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class StructureAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\StructureAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
