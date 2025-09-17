<?php

namespace MetaFox\Profile\Http\Requests\v1\Field\Admin;

use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Profile\Rules\OptionsFieldRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Profile\Http\Controllers\Api\v1\FieldAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends StoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = (int) $this->route('field');

        $parentRules = parent::rules();
        $rules       = [
            'field_name'      => [
                'string', 'required', 'regex:/' . MetaFoxConstant::RESOURCE_IDENTIFIER_REGEX . '/',
                new CaseInsensitiveUnique('user_custom_fields', 'field_name', $id),
                new CaseInsensitiveUnique('user_custom_sections', 'name'),
            ],
            'options.*.label' => ['sometimes', 'max:150', new OptionsFieldRule($id)],
            'options.*.id'    => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_custom_options,id')],
        ];

        /**Don't user change 'edit_type' field*/
        Arr::forget($parentRules, 'edit_type');

        return array_merge($parentRules, $rules);
    }
}
