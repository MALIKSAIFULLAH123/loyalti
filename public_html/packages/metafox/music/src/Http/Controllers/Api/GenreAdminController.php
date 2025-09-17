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
 * Class GenreAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class GenreAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\GenreAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
