<?php

namespace MetaFox\Profile\Http\Requests\v1\Section\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Profile\Http\Controllers\Api\v1\SectionAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'       => [
                'string', 'required', 'regex:/' . MetaFoxConstant::RESOURCE_IDENTIFIER_REGEX . '/',
                new CaseInsensitiveUnique('user_custom_sections', 'name'),
                new CaseInsensitiveUnique('user_custom_fields', 'field_name'),
            ],
            'label'      => ['array', 'required', new TranslatableTextRule()],
            'is_active'  => ['sometimes', 'nullable', new AllowInRule([0, 1])],
            'profile_id' => ['int', 'sometimes'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $emptyPhrasesData = Language::getEmptyPhraseData();
        $labelData        = Language::extractPhraseData('label', $data);

        Arr::set($data, 'label', !empty($labelData) ? $labelData : $emptyPhrasesData);

        return $data;
    }
}
