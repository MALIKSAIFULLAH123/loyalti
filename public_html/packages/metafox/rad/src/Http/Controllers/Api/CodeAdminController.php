<?php

namespace MetaFox\Rad\Http\Controllers\Api;

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
 * Class CodeAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class CodeAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\CodeAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
