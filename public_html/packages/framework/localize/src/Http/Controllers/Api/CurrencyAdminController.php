<?php

namespace MetaFox\Localize\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * | --------------------------------------------------------------------------
 * | Api Gateway
 * | --------------------------------------------------------------------------
 * | stub: /packages/controllers/api_gateway.stub
 * |
 * | This class solves api versioning problem.
 * | DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class CurrencyAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class CurrencyAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\CurrencyAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
