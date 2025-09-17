<?php

namespace MetaFox\User\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * | --------------------------------------------------------------------------
 * | Api Gateway
 * | --------------------------------------------------------------------------
 * | stub: /packages/controllers/api_gateway.stub
 * |
 * | This class solves api versioning problem.
 * | DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class GenderAdminController.
 * @codeCoverageIgnore
 * @ignore
 * @group user
 * @authenticated
 * @admincp
 */
class GenderAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\GenderAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
