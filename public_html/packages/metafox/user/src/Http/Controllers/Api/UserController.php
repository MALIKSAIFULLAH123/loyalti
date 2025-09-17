<?php

namespace MetaFox\User\Http\Controllers\Api;

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
 * Class UserController.
 * @codeCoverageIgnore
 * @ignore
 */
class UserController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\UserController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
