<?php

namespace MetaFox\Report\Http\Requests\v1\ReportItemAggregate\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Report\Support\Browse\Scopes\SortScope;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Report\Http\Controllers\Api\v1\ReportItemAggregateAdminController::index
 */

/**
 * Class IndexRequest.
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
            'q'         => ['sometimes', 'string', 'nullable'],
            'item_type' => ['sometimes', 'string', 'nullable'],
            'sort'      => SortScope::rules(),
            'sort_type' => SortScope::sortTypes(),
            'limit'     => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
        ];
    }

    /**
     * @param  array|int|string|null $key
     * @param  mixed                 $default
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $data = Arr::add($data, 'sort', Browse::SORT_RECENT);
        $data = Arr::add($data, 'sort_type', Browse::SORT_TYPE_DESC);

        return $data;
    }
}
