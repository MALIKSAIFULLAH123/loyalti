<?php

namespace MetaFox\User\Http\Requests\v1\User\Admin;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\User\Http\Requests\v1\User\UpdateRequest as UserUpdateRequest;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserAdminController::updateCustomFields;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateCustomFieldsRequest.
 */
class UpdateCustomFieldsRequest extends UserUpdateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules(): array
    {
        $userId = $this->route('id');
        $rules  = new \ArrayObject([]);

        $user = user();
        if ($userId > 0) {
            $user = UserEntity::getById($userId)->detail;
        }

        CustomFieldFacade::loadFieldEditRules($user, $rules, ['section_type' => CustomField::SECTION_TYPE_USER]);

        return $rules->getArrayCopy();
    }

    public function validated($key = null, $default = null): array
    {
        $data   = parent::validated($key, $default);
        $userId = $this->route('id');
        $user   = $context = user();

        if ($userId > 0) {
            $user = UserEntity::getById($userId)->detail;
        }
        $params = [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'view'         => CustomField::VIEW_ALL,
        ];

        $data = CustomFieldFacade::handleCustomProfileFieldsForEdit($user, $data, $params);
        $data = CustomFieldFacade::filterVisibleRoleFieldsForEdit($context, $user, $data, $params);

        return $data;
    }
}
