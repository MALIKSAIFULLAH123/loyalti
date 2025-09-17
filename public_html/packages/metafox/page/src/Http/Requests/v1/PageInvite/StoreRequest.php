<?php

namespace MetaFox\Page\Http\Requests\v1\PageInvite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Page\Http\Controllers\Api\v1\PageInviteController::store;
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
            'page_id'    => ['required', 'numeric', 'exists:pages,id'],
            'user_ids'   => ['required', 'array'],
            'user_ids.*' => ['required_with:users', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $userIds = Arr::get($data, 'user_ids', []);

        $data['ids'] = collect($userIds)->map(function ($item) {
            if (is_array($item)) {
                return Arr::get($item, 'id', 0);
            }

            return $item;
        })->toArray();

        return $data;
    }
}
