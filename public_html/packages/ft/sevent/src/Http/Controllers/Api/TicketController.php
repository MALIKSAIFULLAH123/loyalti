<?php

namespace Foxexpert\Sevent\Http\Controllers\Api;

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
 * Class SeventController.
 * @ignore
 * @codeCoverageIgnore
 */
class TicketController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\TicketController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
