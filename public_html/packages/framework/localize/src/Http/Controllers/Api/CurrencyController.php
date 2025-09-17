<?php

namespace MetaFox\Localize\Http\Controllers\Api;

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
 * Class CurrencyController.
 * @ignore
 * @codeCoverageIgnore
 */
class CurrencyController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\CurrencyController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
