<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Subscription\Support\Helper;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionCancelReasonController::index
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
        return [];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        Arr::set($data, 'view', Helper::VIEW_FILTER);

        return $data;
    }
}
