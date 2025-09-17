<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class TaggedFriendsRequest.
 */
class TaggedFriendsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page'         => ['sometimes', 'numeric', 'min:1'],
            'limit'        => ['sometimes', 'numeric', new PaginationLimitRule()],
            'item_id'      => ['required', 'numeric'],
            'item_type'    => ['required', 'string'],
            'excluded_ids' => ['sometimes', 'array'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        $excludedIds = Arr::get($data, 'excluded_ids');

        if (is_array($excludedIds)) {
            $excludedIds = array_values(array_filter($excludedIds, function ($id) {
                return is_numeric($id) && $id > 0;
            }));

            $data['excluded_ids'] = $excludedIds;

            if (!count($excludedIds)) {
                unset($data['excluded_ids']);
            }
        }

        return $data;
    }
}
