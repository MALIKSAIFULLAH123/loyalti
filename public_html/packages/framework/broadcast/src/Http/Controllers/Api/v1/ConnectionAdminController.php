<?php

namespace MetaFox\Broadcast\Http\Controllers\Api\v1;

use MetaFox\Broadcast\Http\Resources\v1\Connection\Admin\ConnectionItemCollection as ItemCollection;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Broadcast\Http\Controllers\Api\ConnectionAdminController::$controllers;.
 */

/**
 * Class DriverAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class ConnectionAdminController extends ApiController
{
    /**
     * Browse item.
     *
     * @return mixed
     */
    public function index(): ItemCollection
    {
        $connections = app('core.drivers')
            ->getDrivers(
                'form-settings',
                'broadcast.driver',
                'admin'
            );

        return new ItemCollection($connections);
    }
}
