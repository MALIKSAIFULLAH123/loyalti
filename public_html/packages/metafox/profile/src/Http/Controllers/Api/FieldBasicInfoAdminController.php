<?php

namespace MetaFox\Profile\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Profile\Http\Controllers\Api\FieldBasicInfoAdminController::$controllers;.
 */

/**
 * class FieldBasicInfoAdminController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class FieldBasicInfoAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\FieldBasicInfoAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
