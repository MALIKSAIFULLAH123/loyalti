<?php

namespace MetaFox\Music\Http\Controllers\Api;

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
 * Class AlbumController.
 */
class AlbumController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\AlbumController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
