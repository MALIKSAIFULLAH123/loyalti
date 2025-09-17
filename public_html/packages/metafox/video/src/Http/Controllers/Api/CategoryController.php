<?php

namespace MetaFox\Video\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/*************************************************
 *
 * This class is used to solved api versioning problem.
 * forward request to v1/*.php or v2/*.Controller
 * use this class to ../routes/api.php instead of particular ApiController.
 *
 *************************************************/
class CategoryController extends GatewayController
{
    protected $controllers = [
        'v1' => v1\CategoryController::class,
    ];
}

// template: app/Console/Commands/stubs/scaffold/module_api_gateway.stub
