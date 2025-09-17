<?php

namespace MetaFox\Translation\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController as ApiGatewayController;

/**
 * --------------------------------------------------------------------------
 *  Api Gateway
 * --------------------------------------------------------------------------.
 *
 * This class solves api versioning problem.
 * DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class TranslationGatewayAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class TranslationGatewayAdminController extends ApiGatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\TranslationGatewayAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
