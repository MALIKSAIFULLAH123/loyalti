<?php

namespace MetaFox\Marketplace\Http\Requests\v1\Listing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\ViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;

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
            'q'            => ['sometimes', 'nullable', 'string'],
            'view'         => ['sometimes', 'string', new AllowInRule(ViewScope::getAllowView())],
            'sort'         => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'    => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'when'         => ['sometimes', 'string', new AllowInRule(WhenScope::getAllowWhen())],
            'category_id'  => ['sometimes', 'nullable', 'numeric', 'exists:marketplace_categories,id'],
            'owner_id'     => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
            'user_id'      => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'page'         => ['sometimes', 'numeric', 'min:1'],
            'limit'        => ['sometimes', 'numeric', new PaginationLimitRule()],
            'country_iso'  => ['sometimes', 'string', 'exists:core_countries,country_iso'],
            'bounds_west'  => ['sometimes', 'numeric'],
            'bounds_east'  => ['sometimes', 'numeric'],
            'bounds_south' => ['sometimes', 'numeric'],
            'bounds_north' => ['sometimes', 'numeric'],
            'price_from'   => ['sometimes', 'numeric', 'min:0'],
            'price_to'     => ['sometimes', 'numeric', 'min:0'],
            'is_featured'  => ['sometimes', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!Arr::has($data, 'view')) {
            Arr::set($data, 'view', ViewScope::VIEW_DEFAULT);
        }

        if (!Arr::has($data, 'sort_type')) {
            Arr::set($data, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        }

        if (!Arr::has($data, 'sort')) {
            Arr::set($data, 'sort', SortScope::SORT_DEFAULT);
        }

        if (!Arr::has($data, 'when')) {
            Arr::set($data, 'when', WhenScope::WHEN_DEFAULT);
        }

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        }

        $categoryId = Arr::get($data, 'category_id', MetaFoxConstant::EMPTY_STRING);

        if (MetaFoxConstant::EMPTY_STRING == $categoryId) {
            Arr::set($data, 'category_id', 0);
        }

        if (!Arr::has($data, 'user_id') && !Arr::has($data, 'owner_id')) {
            Arr::set($data, 'user_id', 0);
        }

        if (Arr::has($data, 'owner_id')) {
            Arr::set($data, 'user_id', $data['owner_id']);
        }

        $q = Arr::get($data, 'q');

        if (null === $q) {
            $q = MetaFoxConstant::EMPTY_STRING;
        }

        $q = trim($q);

        if (Str::startsWith($q, '#')) {
            $data['tag'] = ltrim($q, '#');

            $q = MetaFoxConstant::EMPTY_STRING;
        }

        Arr::set($data, 'q', $q);

        return $data;
    }
}
