<?php

namespace MetaFox\Importer\Http\Controllers\Api;

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
 * Class BundleAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class BundleAdminController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1' => v1\BundleAdminController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}

// end
