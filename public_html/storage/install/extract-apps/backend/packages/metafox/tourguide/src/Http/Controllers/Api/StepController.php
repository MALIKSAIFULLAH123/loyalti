<?php

namespace MetaFox\TourGuide\Http\Controllers\Api;

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
 * Class StepController.
 * @codeCoverageIgnore
 * @ignore
 */
class StepController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\StepController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
