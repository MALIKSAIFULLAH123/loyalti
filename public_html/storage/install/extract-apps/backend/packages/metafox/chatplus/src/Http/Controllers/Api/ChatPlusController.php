<?php

namespace MetaFox\ChatPlus\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * Class ChatPlusController.
 */

/*************************************************
 *
 * This class is used to solved api versioning problem.
 * forward request to v1/*.php or v2/*.Controller
 * use this class to ../routes/api.php instead of particular ApiController.
 *
 *************************************************/

/**
 * Class ChatPlusController.
 * @ignore
 * @codeCoverageIgnore
 */
class ChatPlusController extends GatewayController
{
    protected $controllers = [
        'v1' => v1\ChatPlusController::class,
    ];
}
