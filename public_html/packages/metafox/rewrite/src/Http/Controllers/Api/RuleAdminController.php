<?php

namespace MetaFox\Rewrite\Http\Controllers\Api;

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
 * Class RuleAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class RuleAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\RuleAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
