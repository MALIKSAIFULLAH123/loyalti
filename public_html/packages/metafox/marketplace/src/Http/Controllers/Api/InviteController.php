<?php

namespace MetaFox\Marketplace\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * Class InviteController.
 * @ignore
 * @codeCoverageIgnore
 */
class InviteController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\InviteController::class,
    ];
}
