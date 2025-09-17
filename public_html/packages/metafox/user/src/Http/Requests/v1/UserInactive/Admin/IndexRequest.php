<?php

namespace MetaFox\User\Http\Requests\v1\UserInactive\Admin;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserAdminController::inactive();
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class IndexRequest extends \MetaFox\User\Http\Requests\v1\User\Admin\IndexRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules['day'] = ['sometimes', 'integer', 'min:1'];

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['day'])) {
            $data['day'] = 7;
        }

        return $data;
    }
}
