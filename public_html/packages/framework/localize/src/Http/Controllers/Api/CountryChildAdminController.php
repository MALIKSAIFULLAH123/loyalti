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
 * Class CountryChildController.
 * @ignore
 * @codeCoverageIgnore
 */
class CountryChildAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\CountryChildAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
