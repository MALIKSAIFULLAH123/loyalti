<?php

namespace MetaFox\AntiSpamQuestion\Http\Controllers\Api;

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
 * Class QuestionAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class QuestionAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\QuestionAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
