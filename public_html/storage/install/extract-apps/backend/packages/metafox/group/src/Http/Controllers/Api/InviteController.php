<?php

namespace MetaFox\Group\Http\Controllers\Api;

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
 * Class InviteController.
 * @ignore
 * @codeCoverageIgnore
 */
class InviteController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\InviteController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
