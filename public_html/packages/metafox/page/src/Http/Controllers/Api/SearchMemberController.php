<?php

namespace MetaFox\Page\Http\Controllers\Api;

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
 * Class SearchMemberController.
 * @codeCoverageIgnore
 * @ignore
 */
class SearchMemberController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\SearchMemberController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
