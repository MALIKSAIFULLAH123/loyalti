<?php

namespace MetaFox\Comment\Http\Controllers\Api;

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
 * Class PendingAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class PendingAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\PendingAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
