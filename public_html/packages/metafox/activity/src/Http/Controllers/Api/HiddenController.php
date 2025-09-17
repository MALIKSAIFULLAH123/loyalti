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
 * Class HiddenController.
 * @ignore
 * @codeCoverageIgnore
 */
class HiddenController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\HiddenController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
