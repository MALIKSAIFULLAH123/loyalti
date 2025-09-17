<?php

namespace MetaFox\TourGuide\Http\Requests\v1\Step;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\HexColorRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\TourGuide\Http\Controllers\Api\v1\StepController::store
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
    public function rules(): array
    {
        return [
            'title_var'        => ['required', 'array', new TranslatableTextRule()],
            'desc_var'         => ['required', 'array', new TranslatableTextRule()],
            'tour_guide_id'    => ['required', 'numeric', new ExistIfGreaterThanZero('exists:tour_guides,id')],
            'delay'            => ['required', 'integer'],
            'background_color' => ['sometimes', 'nullable', 'string', new HexColorRule()],
            'font_color'       => ['sometimes', 'nullable', 'string', new HexColorRule()],
            'element'          => ['required', 'string'],
            'page_name'        => ['required', 'string'],
            'is_active'        => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'is_completed'     => ['sometimes', 'numeric', new AllowInRule([0, 1])],
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
