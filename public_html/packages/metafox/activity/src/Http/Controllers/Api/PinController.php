<?php

namespace MetaFox\Activity\Http\Controllers\Api;

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
 * Class PinController.
 * @ignore
 * @codeCoverageIgnore
 */
class PinController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\PinController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
