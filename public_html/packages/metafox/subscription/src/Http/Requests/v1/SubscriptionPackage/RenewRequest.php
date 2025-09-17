<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Subscription\Support\Helper;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceController::renew
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class RenewRequest.
 */
class RenewRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'renew_type' => ['sometimes', 'string', new AllowInRule(Helper::getRenewType())],
        ];
    }
}
