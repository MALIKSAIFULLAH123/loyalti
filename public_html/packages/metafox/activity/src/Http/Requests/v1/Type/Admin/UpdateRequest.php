<?php

namespace MetaFox\Activity\Http\Requests\v1\Type\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Activity\Http\Controllers\Api\v1\TypeAdminController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'is_active'                    => ['sometimes', 'boolean'],
            'is_system'                    => ['sometimes', 'boolean'],
            'can_comment'                  => ['sometimes', 'boolean'],
            'can_like'                     => ['sometimes', 'boolean'],
            'can_share'                    => ['sometimes', 'boolean'],
            'can_edit'                     => ['sometimes', 'boolean'],
            'can_create_feed'              => ['sometimes', 'boolean'],
            'can_change_privacy_from_feed' => ['sometimes', 'boolean'],
            'can_redirect_to_detail'       => ['sometimes', 'boolean'],
            'prevent_from_edit_feed_item'  => ['sometimes', 'boolean'],
        ];
    }
}
