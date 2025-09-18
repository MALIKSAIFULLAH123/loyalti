<?php

namespace MetaFox\Saved\Http\Requests\v1\Saved;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Saved\Http\Controllers\Api\v1\SavedController::moveItem;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class MoveItemRequest.
 */
class MoveItemRequest extends FormRequest
{
    public const REMOVE_LIST = 1;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_id'          => ['required', 'numeric', 'exists:saved_items,id'],
            'collection_ids'   => ['sometimes', 'array'],
            'collection_ids.*' => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:saved_lists,id')],
        ];
    }

    public function messages()
    {
        return [
            'collection_ids.*.exists' => __p('saved::validation.collection_not_exists'),
        ];
    }
}
