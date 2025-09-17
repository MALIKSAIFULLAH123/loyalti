<?php

namespace Foxexpert\Sevent\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 | --------------------------------------------------------------------------
 |  Api Gateway
 | --------------------------------------------------------------------------.
 |
 | This class solves api versioning problem.
 | DO NOT IMPLEMENT ACTION HERE.
 | stub: /packages/controllers/admin_api_gateway.stub
 */

/**
 * Class SeventAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class SeventAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\SeventAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
