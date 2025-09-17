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
 * Class BuildController.
 * @ignore
 * @codeCoverageIgnore
 */
class BuildController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\BuildController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
