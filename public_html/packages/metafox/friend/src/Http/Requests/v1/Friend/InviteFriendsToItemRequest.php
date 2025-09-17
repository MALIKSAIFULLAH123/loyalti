<?php

namespace MetaFox\Friend\Http\Requests\v1\Friend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Helper\Pagination;

class InviteFriendsToItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'owner_id'  => ['required', 'numeric', 'exists:user_entities,id'],
            'user_id'   => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'item_type' => ['required', 'string'],
            'item_id'   => ['required', 'numeric'],
            'q'         => ['sometimes', 'nullable', 'string'],
            'limit'     => ['sometimes', 'integer', 'min:1', 'max: ' . Pagination::DEFAULT_MAX_ITEM_PER_PAGE],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $q = Arr::get($data, 'q', MetaFoxConstant::EMPTY_STRING);

        if (null === $q) {
            $q = MetaFoxConstant::EMPTY_STRING;
        }

        $q = trim($q);

        Arr::set($data, 'q', $q);

        if (!Arr::has($data, 'user_id')) {
            Arr::set($data, 'user_id', Auth::id());
        }

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        }

        return $data;
    }
}
