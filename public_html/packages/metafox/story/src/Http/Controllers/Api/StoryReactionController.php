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
 * Class StoryReactionController
 * @codeCoverageIgnore
 * @ignore
 */
class StoryReactionController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\StoryReactionController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
