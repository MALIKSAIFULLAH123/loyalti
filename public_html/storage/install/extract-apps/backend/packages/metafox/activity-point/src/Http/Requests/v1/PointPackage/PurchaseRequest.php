<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\PointPackage;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Payment\Traits\Request\HandlePaymentRequestTrait;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\ActivityPoint\Http\Controllers\Api\v1\PointPackageController::purchase
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class PurchaseRequest.
 */
class PurchaseRequest extends FormRequest
{
    use HandlePaymentRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'payment_gateway'                  => ['required', 'integer', 'exists:payment_gateway,id'],
        ];

        $extraRules = $this->getAdditionalPaymentGatewayRules();

        if (is_array($extraRules)) {
            $rules = array_merge($rules, $extraRules);
        }

        return $rules;
    }
}
