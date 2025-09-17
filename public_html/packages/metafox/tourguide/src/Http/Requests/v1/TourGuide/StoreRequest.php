<?php

namespace MetaFox\TourGuide\Http\Requests\v1\TourGuide;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\TourGuide\Supports\Browse\Scopes\PrivacyScope;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\TourGuide\Http\Controllers\Api\v1\TourGuideController::store
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
        $maxLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;

        return [
            'name'      => ['required', 'string', "max:$maxLength"],
            'privacy'   => ['required', 'integer', new AllowInRule(PrivacyScope::getAllowedPrivates())],
            'url'       => ['required', 'string'],
            'page_name' => ['required', 'string'],
            'is_auto'   => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
