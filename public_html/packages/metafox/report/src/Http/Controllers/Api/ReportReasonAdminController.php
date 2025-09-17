<?php

namespace MetaFox\Report\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * --------------------------------------------------------------------------
 * Api Gateway
 * --------------------------------------------------------------------------
 * stub: /packages/controllers/api_gateway.stub.
 *
 * This class solves api versioning problem.
 * DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class ReportReasonAdminController.
 * @ignore
 * @codeCoverageIgnore
 */
class ReportReasonAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\ReportReasonAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
