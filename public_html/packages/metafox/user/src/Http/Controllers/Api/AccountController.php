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
 * Class AccountController.
 * @codeCoverageIgnore
 * @ignore
 */
class AccountController extends GatewayController
{
    /**
     * @var array<string, string>
     */
    protected $controllers = [
        'v1'   => v1\AccountController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
