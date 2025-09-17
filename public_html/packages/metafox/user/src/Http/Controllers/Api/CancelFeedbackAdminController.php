<?php

namespace MetaFox\User\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 | --------------------------------------------------------------------------
 | Api Gateway
 | --------------------------------------------------------------------------
 | stub: /packages/controllers/api_gateway.stub
 |
 | This class solves api versioning problem.
 | DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class CancelFeedbackAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class CancelFeedbackAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\CancelFeedbackAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
