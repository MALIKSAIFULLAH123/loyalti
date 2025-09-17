<?php

namespace MetaFox\User\Http\Requests\v1\User\Admin;

use MetaFox\User\Http\Requests\v1\User\UpdateRequest as UserUpdateRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserAdminController::updateProfilePrivacy;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateProfilePrivacyRequest.
 */
class UpdateProfilePrivacyRequest extends UserUpdateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = new \ArrayObject([
            'privacy' => ['sometimes', 'nullable', 'array'],
        ]);

        return $rules->getArrayCopy();
    }

    /**
     * @param  string               $key
     * @param  mixed                $default
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        return parent::validated($key, $default);
    }
}
