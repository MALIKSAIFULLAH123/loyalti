<?php

namespace MetaFox\Menu\Http\Requests\v1\MenuItem\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Core\Http\Controllers\Api\v1\MenuItemAdminController::index;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 *
 * query parameters
 */
class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'menu_id'      => ['sometimes', 'nullable', 'numeric'],
            'menu_item_id' => ['sometimes', 'nullable', 'numeric'],
            'q'            => ['sometimes', 'nullable', 'string'],
            'package_id'   => ['sometimes', 'nullable', 'string'],
            'resolution'   => ['sometimes', 'nullable', 'string'],
            'is_active'    => ['sometimes', 'nullable', 'numeric', new AllowInRule([0, 1])],
            'page'         => ['sometimes', 'numeric', 'min:1'],
            'limit'        => ['sometimes', 'numeric', 'min:10'],
        ];
    }
}
