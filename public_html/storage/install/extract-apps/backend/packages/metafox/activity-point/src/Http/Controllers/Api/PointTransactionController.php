<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api;

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
 * Class PointTransactionController.
 * @codeCoverageIgnore
 * @ignore
 * @authenticated
 * @group activitypoint
 */
class PointTransactionController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\PointTransactionController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
