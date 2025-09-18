<?php

namespace MetaFox\TourGuide\Http\Requests\v1\TourGuide\Admin;

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
 * @link \MetaFox\TourGuide\Http\Controllers\Api\v1\TourGuideAdminController::update
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
        $maxLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;

        return [
            'name'      => ['required', 'string', "max:$maxLength"],
            'url'       => ['required', 'string'],
            'privacy'   => ['required', 'integer', new AllowInRule(PrivacyScope::getAllowedPrivates())],
            'is_auto'   => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'is_active' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
