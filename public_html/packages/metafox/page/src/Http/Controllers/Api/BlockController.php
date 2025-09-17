<?php
namespace MetaFox\Page\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * Class BlockController.
 */
class BlockController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\BlockController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
