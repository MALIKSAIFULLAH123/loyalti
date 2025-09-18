<?php

namespace MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\DurationScope;
use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\ViewAdminScope;
use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\ViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\LiveStreaming\Http\Controllers\Api\v1\LiveVideoAdminController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest
 */
class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'q'            => ['sometimes', 'nullable', 'string'],
            'view'         => ViewAdminScope::rules(),
            'sort'         => SortScope::rules(),
            'sort_type'    => SortScope::sortTypes(),
            'owner_name'   => ['sometimes', 'nullable', 'string'],
            'user_name'    => ['sometimes', 'nullable', 'string'],
            'created_from' => ['sometimes', 'nullable', 'string'],
            'created_to'   => ['sometimes', 'nullable', 'string', 'after:created_from'],
            'page'         => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'        => ['sometimes', 'nullable', 'integer', new PaginationLimitRule(20, 500)],
            'duration'     => DurationScope::rules(),
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['view'])) {
            $data['view'] = ViewScope::VIEW_DEFAULT;
        }

        if (!isset($data['sort'])) {
            $data['sort'] = SortScope::SORT_DEFAULT;
        }

        if (!isset($data['sort_type'])) {
            $data['sort_type'] = SortScope::SORT_TYPE_DEFAULT;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        $isSearching = Arr::has($data, 'q');

        if (!$isSearching) {
            Arr::set($data, 'q', MetaFoxConstant::EMPTY_STRING);

            return $data;
        }

        $q = Arr::get($data, 'q');

        if (null === $q) {
            $q = MetaFoxConstant::EMPTY_STRING;
        }

        $q = trim($q);

        Arr::set($data, 'q', $q);

        Arr::set($data, 'view', Browse::VIEW_SEARCH);

        return $data;
    }
}
