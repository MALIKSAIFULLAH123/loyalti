<?php

namespace MetaFox\GettingStarted\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

class TodoListController extends GatewayController
{
    protected $controllers = [
        'v1' => v1\TodoListController::class,
    ];
}
