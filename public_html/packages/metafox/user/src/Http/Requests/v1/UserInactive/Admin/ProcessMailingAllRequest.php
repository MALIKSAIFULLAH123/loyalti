<?php

namespace MetaFox\User\Http\Requests\v1\UserInactive\Admin;

use Illuminate\Support\Arr;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserAdminController::processMailingAll();
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ProcessMailingAllRequest.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ProcessMailingAllRequest extends \MetaFox\User\Http\Requests\v1\UserInactive\Admin\IndexRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = parent::rules();

        Arr::forget($rules, ['limit', 'page']);

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        Arr::forget($data, ['limit', 'page']);

        return $data;
    }
}
