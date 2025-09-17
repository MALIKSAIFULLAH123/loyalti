<?php

namespace MetaFox\Search\Http\Controllers\Api;

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
 * Class ReindexAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class ReindexAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\ReindexAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
