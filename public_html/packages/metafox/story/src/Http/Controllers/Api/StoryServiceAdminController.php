<?php

namespace MetaFox\Story\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * --------------------------------------------------------------------------
 * Api Gateway
 * --------------------------------------------------------------------------
 * stub: /packages/controllers/api_gateway.stub
 *
 * This class solve api versioning problem.
 * DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class StoryServiceAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class StoryServiceAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\StoryServiceAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
