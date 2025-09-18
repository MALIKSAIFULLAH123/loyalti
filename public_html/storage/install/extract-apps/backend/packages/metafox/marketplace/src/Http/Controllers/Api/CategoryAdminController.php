<?php

namespace MetaFox\Marketplace\Http\Controllers\Api;

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
class CategoryAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\CategoryAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
