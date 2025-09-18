<?php

namespace MetaFox\Marketplace\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * Class ListingController.
 * @ignore
 * @codeCoverageIgnore
 */
class ListingController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\ListingController::class,
    ];
}
