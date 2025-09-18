<?php

namespace MetaFox\Marketplace\Http\Requests\v1\Invite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

class InviteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'listing_id' => ['required', 'numeric', 'exists:marketplace_listings,id'],
            'user_ids'   => ['sometimes', 'array'],
            'user_ids.*' => ['required_with:user_ids', 'numeric',new ExistIfGreaterThanZero('exists:users,id')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!Arr::has($data, 'user_ids')) {
            Arr::set($data, 'user_ids', []);
        }

        return $data;
    }
}
