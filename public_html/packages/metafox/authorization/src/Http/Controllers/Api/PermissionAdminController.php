<?php

namespace MetaFox\Authorization\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * | --------------------------------------------------------------------------
 * | Api Gateway
 * | --------------------------------------------------------------------------
 * | stub: /packages/controllers/api_gateway.stub
 * |
 * | This class solves api versioning problem.
 * | DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class PermissionAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class PermissionAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\PermissionAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
