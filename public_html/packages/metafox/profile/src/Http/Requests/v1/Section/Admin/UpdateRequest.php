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
 * @link \MetaFox\Profile\Http\Controllers\Api\v1\SectionAdminController::update
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
        $id = $this->route('section');

        return [
            'name'        => [
                'string', 'required', 'regex:/' . MetaFoxConstant::RESOURCE_IDENTIFIER_REGEX . '/',
                new CaseInsensitiveUnique('user_custom_sections', 'name', $id),
                new CaseInsensitiveUnique('user_custom_fields', 'field_name'),
            ],
            'label'       => ['array', 'required', new TranslatableTextRule()],
            'description' => ['string', 'sometimes', 'nullable'],
            'is_active'   => ['sometimes', 'nullable', new AllowInRule([0, 1])],
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
