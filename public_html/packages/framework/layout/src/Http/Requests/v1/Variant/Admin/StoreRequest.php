<?php

namespace MetaFox\Layout\Http\Requests\v1\Variant\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Layout\Http\Controllers\Api\v1\VariantAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest
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
            'theme_id'  => ['string', 'required', 'exists:layout_themes,theme_id'],
            'variant_id' => ['string', 'required', new CaseInsensitiveUnique('layout_variants', 'variant_id')],
            'title'     => ['string', 'required'],
        ];
    }
}
