<?php

namespace MetaFox\Like\Http\Requests\v1\Reaction\Admin;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Like\Http\Controllers\Api\v1\ReactionAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest
 */
class UpdateRequest extends StoreRequest
{
    protected function isEdit(): bool
    {
        return true;
    }
}
