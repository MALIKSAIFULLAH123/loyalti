<?php

namespace MetaFox\Chat\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

class ChatRoomController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\ChatRoomController::class,
    ];
}
