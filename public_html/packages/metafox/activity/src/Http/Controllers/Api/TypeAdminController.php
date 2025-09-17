<?php

namespace MetaFox\Activity\Http\Controllers\Api;

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
 * Class TypeAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class TypeAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\TypeAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
