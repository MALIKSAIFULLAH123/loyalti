<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\PointTransaction;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Support\ActivityPoint;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\ActivityPoint\Http\Controllers\Api\v1\PointTransactionController::index
 * stub: /packages/requests/api_action_request.stub
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
        $allowedTypes = array_values(ActivityPoint::ALLOW_TYPES);

        return [
            'type'      => ['sometimes', 'int', 'nullable', new AllowInRule($allowedTypes)],
            'from'      => ['sometimes', 'date', 'nullable'],
            'to'        => ['sometimes', 'date', 'nullable', 'after:from'],
            'sort'      => SortScope::rules(),
            'sort_type' => SortScope::sortTypes(),
            'page'      => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'     => ['sometimes', 'nullable', 'integer', new PaginationLimitRule(null, 500)],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = Arr::add($data, 'type', ActivityPoint::TYPE_ALL);
        $data = Arr::add($data, 'sort', SortScope::SORT_DEFAULT);
        $data = Arr::add($data, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        $data = Arr::add($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        if (Arr::has($data, 'from')) {
            $data['from'] = Carbon::create($data['from'])->startOfDay();
        }

        if (Arr::has($data, 'to')) {
            $data['to'] = Carbon::create($data['to'])->endOfDay();
        }

        return $data;
    }
}
