<?php

namespace MetaFox\Sticker\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * --------------------------------------------------------------------------
 *  Api Gateway
 * --------------------------------------------------------------------------.
 *
 * This class solves api versioning problem.
 * DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class CategoryController.
 * @ignore
 * @codeCoverageIgnore
 */
class StickerSetController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\StickerSetController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
