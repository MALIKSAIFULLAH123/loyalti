<?php

namespace MetaFox\Sticker\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * --------------------------------------------------------------------------
 * Api Gateway
 * --------------------------------------------------------------------------
 * stub: /packages/controllers/api_gateway.stub.
 *
 * This class solve api versioning problem.
 * DO NOT IMPLEMENT ACTION HERE.
 */

/**
 * Class StickerSetAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class StickerSetAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\StickerSetAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
