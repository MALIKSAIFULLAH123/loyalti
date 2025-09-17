<?php

namespace MetaFox\Ban\Http\Requests\v1\BanRule\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Ban\Facades\Ban;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Ban\Http\Controllers\Api\v1\BanRuleAdminController::create
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class CreateFormRequest.
 */
class CreateFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'resourceName' => ['required', 'string', new AllowInRule(Ban::getAllowedBanRuleTypes())],
        ];
    }
}
