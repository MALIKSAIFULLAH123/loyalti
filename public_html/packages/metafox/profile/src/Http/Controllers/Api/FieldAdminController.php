<?php

namespace MetaFox\Profile\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * --------------------------------------------------------------------------
 * Api Gateway
 * --------------------------------------------------------------------------
 * stub: /packages/controllers/api_gateway.stub.
 *
 * This class solve api versioning problem.
 * DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * class FieldAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class FieldAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\FieldAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
