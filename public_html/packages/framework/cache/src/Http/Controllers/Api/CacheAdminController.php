<?php

namespace MetaFox\Cache\Http\Controllers\Api;

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
 * Class CacheAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class CacheAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\CacheAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
