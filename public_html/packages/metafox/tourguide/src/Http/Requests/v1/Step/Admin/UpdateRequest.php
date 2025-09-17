<?php

namespace MetaFox\TourGuide\Http\Requests\v1\Step\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\HexColorRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\TourGuide\Http\Controllers\Api\v1\StepAdminController::update
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
    public function rules(): array
    {
        return [
            'title_var'        => ['required', 'array', new TranslatableTextRule()],
            'desc_var'         => ['required', 'array', new TranslatableTextRule()],
            'delay'            => ['required', 'integer'],
            'background_color' => ['sometimes', 'nullable', 'string', new HexColorRule()],
            'font_color'       => ['sometimes', 'nullable', 'string', new HexColorRule()],
            'is_active'        => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        Arr::set($data, 'title_var', Language::extractPhraseData('title_var', $data));
        Arr::set($data, 'desc_var', Language::extractPhraseData('desc_var', $data));

        return $data;
    }
}
