<?php

namespace MetaFox\Layout\Http\Controllers\Api;

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
 * Class SnippetAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class SnippetAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\SnippetAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
