<?php

namespace MetaFox\Advertise\Http\Requests\v1\Sponsor\Admin;

use MetaFox\Advertise\Http\Requests\v1\Sponsor\UpdateRequest as MainRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Advertise\Http\Controllers\Api\v1\SponsorAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends MainRequest
{
    protected function isAdminCP(): bool
    {
        return true;
    }
}
