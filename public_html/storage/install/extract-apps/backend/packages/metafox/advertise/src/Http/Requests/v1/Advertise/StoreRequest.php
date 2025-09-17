<?php

namespace MetaFox\Advertise\Http\Requests\v1\Advertise;

use MetaFox\Advertise\Http\Requests\v1\Advertise\Admin\StoreRequest as AdminRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Advertise\Http\Controllers\Api\v1\AdvertiseController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends AdminRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = parent::rules();

        return $rules;
    }

    protected function isAdminCP(): bool
    {
        return false;
    }
}
