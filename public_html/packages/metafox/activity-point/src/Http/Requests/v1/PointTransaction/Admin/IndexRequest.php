<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\PointTransaction\Admin;

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
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\ActivityPoint\Http\Controllers\Api\v1\PointTransactionAdminController::index
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
            'q'          => ['sometimes', 'string', 'nullable'],
            'type'       => ['sometimes', 'int', 'nullable', new AllowInRule($allowedTypes)],
            'from'       => ['sometimes', 'date', 'nullable'],
            'to'         => ['sometimes', 'date', 'nullable'],
            'sort'       => SortScope::rules(),
            'sort_type'  => SortScope::sortTypes(),
            'page'       => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'      => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
            'package_id' => ['sometimes', 'nullable', 'string'],
            'action_id'  => ['sometimes', 'nullable', 'numeric'],
            'user_id'    => ['sometimes', 'nullable', 'numeric'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Default value
        $data = Arr::add($data, 'package_id', 'all');
        $data = Arr::add($data, 'type', ActivityPoint::TYPE_ALL);
        $data = Arr::add($data, 'sort', SortScope::SORT_DEFAULT);
        $data = Arr::add($data, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        $data = Arr::add($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        if (array_key_exists('from', $data)) {
            $data['from'] = Carbon::create($data['from'])->startOfDay();
        }

        if (array_key_exists('to', $data)) {
            $data['to'] = Carbon::create($data['to'])->endOfDay();
        }

        return $data;
    }
}
