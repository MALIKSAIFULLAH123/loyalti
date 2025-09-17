<?php

namespace MetaFox\Core\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * --------------------------------------------------------------------------
 *  Api Gateway
 * --------------------------------------------------------------------------.
 *
 * This class solves api versioning problem.
 * DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class DashboardAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class DashboardAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\DashboardAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
