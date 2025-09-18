<?php

namespace MetaFox\LiveStreaming\Http\Controllers\Api;

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
 * Class CategoryAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class StreamingServiceAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\StreamingServiceAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
