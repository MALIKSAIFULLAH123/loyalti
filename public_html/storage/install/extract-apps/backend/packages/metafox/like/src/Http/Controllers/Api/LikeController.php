<?php

namespace MetaFox\Like\Http\Controllers\Api;

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
 * Class CategoryController.
 * @ignore
 * @codeCoverageIgnore
 */
class LikeController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\LikeController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
