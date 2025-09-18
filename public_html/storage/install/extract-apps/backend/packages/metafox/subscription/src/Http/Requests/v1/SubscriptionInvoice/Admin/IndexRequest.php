<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Subscription\Support\Helper;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceAdminController::index
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
            'member_name'    => ['sometimes', 'nullable', 'string'],
            'id'             => ['sometimes', 'nullable', 'numeric'],
            'package_id'     => ['sometimes', 'nullable'],
            'payment_status' => ['sometimes', 'nullable', new AllowInRule(Helper::getPaymentStatusForSearching())],
            'reason'         => ['sometimes', 'nullable', 'numeric', 'exists:subscription_cancel_reasons,id'],
            'statistic'      => ['sometimes', new AllowInRule([Helper::STATISTICS_ALL, Helper::STATISTICS_CUSTOM])],
            'statistic_from' => ['sometimes', 'nullable', 'date'],
            'statistic_to'   => ['sometimes', 'nullable', 'date', 'after:statistic_from'],
            'limit'          => ['sometimes', 'numeric', 'between:1,' . Pagination::DEFAULT_MAX_ITEM_PER_PAGE],
            'page'           => ['sometimes', 'numeric', 'min:1'],
            'sort_type'      => ['sometimes', new AllowInRule([Browse::SORT_TYPE_ASC, Browse::SORT_TYPE_DESC])],
            'sort'           => ['sometimes', new AllowInRule(['subscription_invoices.activated_at', 'subscription_invoices.expired_at', 'subscription_user_cancel_reasons.created_at'])],
        ];
    }

    public function messages(): array
    {
        return [
            'id.numeric' => __p('subscription::admin.invoice_id_must_be_numeric'),
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        $isSearch = false;

        if (Arr::has($data, 'member_name')) {
            Arr::set($data, 'member_name', trim(Arr::get($data, 'member_name')));
            $isSearch = true;
        }

        if (Arr::has($data, 'id')) {
            Arr::set($data, 'id', trim(Arr::get($data, 'id')));
            $isSearch = true;
        }

        if (Arr::has($data, 'package_id')) {
            Arr::set($data, 'package_id', trim(Arr::get($data, 'package_id')));
            $isSearch = true;
        }

        if ($isSearch) {
            Arr::set($data, 'view', Browse::VIEW_SEARCH);
        }

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        }

        return $data;
    }
}
