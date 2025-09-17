<?php

namespace Foxexpert\Sevent\Http\Requests\v1\Invoice;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Foxexpert\Sevent\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \Foxexpert\Sevent\Http\Controllers\Api\v1\InvoiceController::index
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
            'status'     => ['sometimes', 'nullable', 'string'],
            'sevent_id' => ['sometimes', 'numeric', 'exists:sevents,id'],
            'ticket_id' => ['sometimes', 'numeric', 'exists:sevent_tickets,id'],
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

        if (Arr::has($data, 'from')) {
            $data['from'] = Carbon::create($data['from'])->startOfDay();
        }

        if (Arr::has($data, 'to')) {
            $data['to'] = Carbon::create($data['to'])->endOfDay();
        }
        
        return $data;
    }
}
