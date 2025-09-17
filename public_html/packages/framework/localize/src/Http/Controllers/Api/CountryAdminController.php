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
 * Class CountryController.
 * @ignore
 * @codeCoverageIgnore
 */
class CountryAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\CountryAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
