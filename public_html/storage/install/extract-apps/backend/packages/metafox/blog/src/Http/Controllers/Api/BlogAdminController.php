<?php

namespace MetaFox\Blog\Http\Controllers\Api;

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
 * Class BlogAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class BlogAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\BlogAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
