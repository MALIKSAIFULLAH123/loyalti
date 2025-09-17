<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Subscription\Support\Helper;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionPackageAdminController::index
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
        $types = Helper::getItemType();

        $statuses = Helper::getItemStatus();

        $statisticOptions = Arr::pluck(Helper::getStatisticOptions(), 'value');

        return [
            'status'                 => ['sometimes', 'nullable', 'string', new AllowInRule($statuses)],
            'q'                      => ['sometimes', 'nullable', 'string'],
            'view'                   => ['sometimes', 'string', new AllowInRule(Helper::getItemView(true))],
            'type'                   => ['sometimes', 'nullable', 'string', new AllowInRule($types)],
            'payment_statistic'      => ['sometimes', 'nullable', new AllowInRule($statisticOptions)],
            'payment_statistic_from' => ['sometimes', 'nullable', 'date'],
            'payment_statistic_to'   => ['sometimes', 'nullable', 'date', 'after:payment_statistic_from'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        Arr::set($data, 'is_admincp', true);

        $isSearch = Arr::hasAny($data, ['q', 'status', 'type', 'payment_statistic']);

        switch ($isSearch) {
            case true:
                Arr::set($data, 'view', Browse::VIEW_SEARCH);
                break;
            default:
                Arr::set($data, 'view', Helper::VIEW_ADMINCP);
                break;
        }

        if (!Arr::has($data, 'type')) {
            Arr::set($data, 'type', null);
        }

        if (!Arr::has($data, 'q')) {
            Arr::set($data, 'q', MetaFoxConstant::EMPTY_STRING);
        }

        // Search with only whitespaces shall works like search with empty string
        Arr::set($data, 'q', trim(Arr::get($data, 'q')));

        return $data;
    }
}
