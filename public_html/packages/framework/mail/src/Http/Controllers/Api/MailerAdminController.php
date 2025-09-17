<?php

namespace MetaFox\Mail\Http\Controllers\Api;

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
 * Class MailerAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class MailerAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\MailerAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
