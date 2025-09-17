<?php

namespace MetaFox\Menu\Http\Controllers\Api;

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
 * Class MenuAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class MenuAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\MenuAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
