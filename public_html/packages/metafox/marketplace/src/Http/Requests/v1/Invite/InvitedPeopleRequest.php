<?php

namespace MetaFox\Marketplace\Http\Requests\v1\Invite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

class InvitedPeopleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'listing_id' => ['required', 'numeric', 'exists:marketplace_listings,id'],
            'limit'      => ['sometimes', 'numeric', new PaginationLimitRule()],
            'page'       => ['sometimes', 'numeric', 'min:1'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        }

        return $data;
    }
}
