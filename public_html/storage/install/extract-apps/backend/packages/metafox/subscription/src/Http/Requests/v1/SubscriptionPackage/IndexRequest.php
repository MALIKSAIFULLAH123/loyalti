<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage;

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
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionPackageController::index
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
            'q'      => ['sometimes', 'string'],
            'view'   => ['sometimes', 'string', new AllowInRule(Helper::getItemView())],
            'page'   => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        Arr::set($data, 'view', Helper::VIEW_FILTER);

        Arr::set($data, 'status', Helper::STATUS_ACTIVE);

        if (!Arr::has($data, 'q')) {
            Arr::set($data, 'q', MetaFoxConstant::EMPTY_STRING);
        }

        // Search with only whitespaces shall works like search with empty string
        Arr::set($data, 'q', trim(Arr::get($data, 'q')));

        // Set view as view search whenever a search keyword exists
        if (MetaFoxConstant::EMPTY_STRING != Arr::get($data, 'q')) {
            Arr::set($data, 'view', Browse::VIEW_SEARCH);
        }

        return $data;
    }
}
