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
 * Class MenuItemAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class MenuItemAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\MenuItemAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
