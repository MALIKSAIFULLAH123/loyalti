<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionPackageAdminController::active
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ActiveRequest.
 */
class ActiveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'active' => ['required', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $isActive = Arr::get($data, 'is_active');

        if (is_numeric($isActive)) {
            Arr::set($data, 'is_active', (bool) $isActive);
        }

        return $data;
    }
}
