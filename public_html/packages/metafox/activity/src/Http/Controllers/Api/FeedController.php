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
 * Class FeedController.
 * @ignore
 * @codeCoverageIgnore
 */
class FeedController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\FeedController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
