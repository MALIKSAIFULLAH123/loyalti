<?php

namespace MetaFox\Marketplace\Http\Requests\v1\Invoice;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Marketplace\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Marketplace\Http\Controllers\Api\v1\InvoiceController::index
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
    public function rules()
    {
        return [
            'view'       => ['sometimes', new AllowInRule(ViewScope::getAllowView())],
            'status'     => ['sometimes', 'nullable', 'string', new AllowInRule(ListingFacade::getPaymentStatus())],
            'listing_id' => ['sometimes', 'numeric', 'exists:marketplace_listings,id'],
            'from'       => ['sometimes', 'date', 'nullable'],
            'to'         => ['sometimes', 'date', 'nullable', 'after_or_equal:from'],
            'page'       => ['sometimes', 'numeric', 'min:1'],
            'limit'      => ['sometimes', 'numeric', 'min:1'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        }

        $timezone = Carbon::parse(MetaFox::clientDate())->timezoneName;

        if (Arr::has($data, 'from') && MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.9', '<')) {
            $from = Carbon::parse($data['from'])
                ->setTimezone($timezone)
                ->startOfDay()
                ->utc();

            $data['from'] = $from->toIso8601ZuluString('millisecond');
        }

        if (Arr::has($data, 'to') && MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.9', '<')) {
            $to = Carbon::parse($data['to'])
                ->setTimezone($timezone)
                ->endOfDay()
                ->utc();

            $data['to'] = $to->toIso8601ZuluString('millisecond');
        }

        if (Arr::get($data, 'status') == ListingFacade::getAllPaymentStatus()) {
            Arr::forget($data, 'status');
        }

        return $data;
    }
}
