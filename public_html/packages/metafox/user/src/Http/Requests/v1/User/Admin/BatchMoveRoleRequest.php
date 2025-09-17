<?php

namespace MetaFox\User\Http\Requests\v1\User\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use MetaFox\Authorization\Models\Role;
use MetaFox\User\Rules\AssignRoleRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class BatchMoveRoleRequest.
 */
class BatchMoveRoleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $context = user();

        return [
            'role_id'    => ['required', 'integer', Rule::exists(Role::class, 'id'), new AssignRoleRule($context)],
            'user_ids'   => ['required', 'array'],
            'user_ids.*' => ['sometimes', 'numeric', 'exists:user_entities,id'],
        ];
    }
}
