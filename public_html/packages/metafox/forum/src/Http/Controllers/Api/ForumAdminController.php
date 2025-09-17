<?php

namespace MetaFox\Forum\Http\Controllers\Api;

use MetaFox\Forum\Http\Controllers\Api\v1\ForumAdminController as v1;
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
 * Class ForumAdminController.
 */
class ForumAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
