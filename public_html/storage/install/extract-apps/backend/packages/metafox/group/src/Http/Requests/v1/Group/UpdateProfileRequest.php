<?php

namespace MetaFox\Group\Http\Requests\v1\Group;

use ArrayObject;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

/**
 * Class UpdateProfileRequest.
 */
class UpdateProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules(): array
    {
        $rules = new ArrayObject([]);

        CustomFieldFacade::loadFieldEditRules(user(), $rules, ['section_type' => CustomField::SECTION_TYPE_GROUP]);

        return $rules->getArrayCopy();
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data = CustomFieldFacade::handleCustomProfileFieldsForEdit(user(), $data, [
            'section_type' => CustomField::SECTION_TYPE_GROUP,
            'view'         => CustomField::VIEW_ALL,
        ]);

        return $data;
    }
}
