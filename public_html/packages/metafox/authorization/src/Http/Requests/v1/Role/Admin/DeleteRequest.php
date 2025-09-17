<?php

namespace MetaFox\Authorization\Http\Requests\v1\Role\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Authorization\Support\Support;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\UserRole;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\RoleAdminController::delete
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class DeleteRequest.
 */
class DeleteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'deleted_id'     => ['required', 'numeric', 'exists:auth_roles,id'],
            'alternative_id' => ['required_if:delete_option,' . Support::DELETE_OPTION_MIGRATION, 'numeric', 'exists:auth_roles,id', 'different:deleted_id'],
            'delete_option'  => ['required', new AllowInRule($this->getDeleteOptions())],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        /*
         * TODO: Update this place when implementing deactive user accounts
         */
        if (Support::DELETE_OPTION_PERMANENTLY == Arr::get($data, 'delete_option')) {
            Arr::set($data, 'alternative_id', UserRole::BANNED_USER_ID);
        }

        return $data;
    }

    protected function getDeleteOptions(): array
    {
        return array_column(resolve(RoleRepositoryInterface::class)->getDeleteOptions(), 'value');
    }

    public function messages()
    {
        return [
            'alternative_id.required' => __p('user::admin.please_choose_an_alternative_role'),
            'alternative_id.numeric'  => __p('user::admin.please_choose_an_alternative_role'),
            'alternative_id.exists'   => __p('user::admin.please_choose_an_alternative_role'),
        ];
    }
}
