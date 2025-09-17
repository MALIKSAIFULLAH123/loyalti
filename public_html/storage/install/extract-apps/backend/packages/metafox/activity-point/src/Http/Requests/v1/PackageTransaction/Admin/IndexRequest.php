<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\PackageTransaction\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Support\Browse\Scopes\PackagePurchase\SortScope;
use MetaFox\ActivityPoint\Support\Browse\Scopes\PackagePurchase\StatusScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\ActivityPoint\Http\Controllers\Api\v1\PackageTransactionAdminController::index
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
        return [
            'full_name'      => ['sometimes', 'string', 'nullable'],
            'package_id'     => ['sometimes', 'integer', 'min:1'],
            'status'         => ['sometimes', 'string', 'nullable', new AllowInRule(StatusScope::getAllowStatus())],
            'transaction_id' => ['sometimes', 'string', 'nullable'],
            'gateway_id'     => ['sometimes', 'integer', 'exists:payment_gateway,id'],
            'from'           => ['sometimes', 'date', 'nullable'],
            'to'             => ['sometimes', 'date', 'nullable'],
            'sort'           => SortScope::rules(),
            'sort_type'      => SortScope::sortTypes(),
            'page'           => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'          => ['sometimes', 'nullable', 'integer', new PaginationLimitRule(null, 500)],
            'id'             => ['sometimes', 'integer', 'exists:apt_package_purchases,id'],
        ];
    }

    /**
     * @param array<string, mixed>|int|string|null $key
     * @param mixed                                $default
     * @return array<string, mixed>
     */
    public function validated(mixed $key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $data = Arr::add($data, 'sort', SortScope::SORT_DEFAULT);
        $data = Arr::add($data, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        $data = Arr::add($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $data = Arr::add($data, 'item_type', 'activitypoint_package_purchase');

        if (Arr::has($data, 'from')) {
            $data['from'] = Carbon::create($data['from'])->startOfDay();
        }

        if (Arr::has($data, 'to')) {
            $data['to'] = Carbon::create($data['to'])->endOfDay();
        }

        if (!Arr::has($data, 'status')) {
            Arr::set($data, 'status', StatusScope::STATUS_ALL);
        }

        return $data;
    }
}
