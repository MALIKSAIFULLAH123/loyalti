<?php

namespace MetaFox\Report\Http\Controllers\Api;

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
 * Class ReportItemController.
 * @ignore
 * @codeCoverageIgnore
 */
class ReportItemController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\ReportItemController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
