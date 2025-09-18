<?php

namespace MetaFox\Socialite\Http\Controllers\Api;

use MetaFox\Platform\Http\Controllers\Api\GatewayController;

/**
 * Class SocialAccountController.
 * @codeCoverageIgnore
 * @ignore
 */
class SocialAccountController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\SocialAccountController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
