<?php

namespace MetaFox\Announcement\Http\Controllers\Api;

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
class AnnouncementViewController extends GatewayController
{
    /**
     * @var string[]
     */
    protected $controllers = [
        'v1'   => v1\AnnouncementViewController::class,
    ];

    // DO NOT IMPLEMENT ACTION HERE.
}
