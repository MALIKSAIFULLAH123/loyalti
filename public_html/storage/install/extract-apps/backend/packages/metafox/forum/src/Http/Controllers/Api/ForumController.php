<?php

namespace MetaFox\Forum\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

class ForumController extends GatewayController
{
    protected $controllers = [
        'v1' => v1\ForumController::class,
    ];
}
