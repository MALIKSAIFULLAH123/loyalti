<?php

namespace MetaFox\GettingStarted\Http\Requests\v1\TodoList\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\GettingStarted\Support\Helper;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q'          => ['sometimes', 'nullable', 'string'],
            'limit'      => ['sometimes', 'numeric', new PaginationLimitRule()],
            'resolution' => ['sometimes', 'string', new AllowInRule([Helper::ALL, MetaFoxConstant::RESOLUTION_WEB, MetaFoxConstant::RESOLUTION_MOBILE])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['q'])) {
            $data['q'] = '';
        }

        if (!array_key_exists('limit', $data)) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['resolution'])) {
            $data['resolution'] = Helper::ALL;
        }

        return $data;
    }
}
