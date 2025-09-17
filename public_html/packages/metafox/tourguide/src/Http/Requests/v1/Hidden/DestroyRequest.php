<?php

namespace MetaFox\TourGuide\Http\Requests\v1\Hidden;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\TourGuide\Http\Controllers\Api\v1\HiddenController::destroy()
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class DestroyRequest.
 */
class DestroyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'tour_guide_id' => ['required', 'numeric', new ExistIfGreaterThanZero('exists:tour_guides,id')],
        ];
    }
}
