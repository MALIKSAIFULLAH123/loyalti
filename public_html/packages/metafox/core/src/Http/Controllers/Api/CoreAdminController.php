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
 * Class CoreAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class CoreAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\CoreAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
