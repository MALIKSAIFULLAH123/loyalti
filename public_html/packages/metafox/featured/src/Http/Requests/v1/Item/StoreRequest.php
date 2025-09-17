<?php

namespace MetaFox\Featured\Http\Requests\v1\Item;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Featured\Rules\PackageRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Featured\Http\Controllers\Api\v1\ItemController::store
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
            'item_type'  => ['required', 'string'],
            'item_id'    => ['required', 'integer', 'min:1'],
            'package_id' => ['required', 'integer', 'exists:featured_packages,id', new PackageRule()],
        ];
    }
}
