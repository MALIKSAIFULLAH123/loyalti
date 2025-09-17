<?php

namespace MetaFox\Page\Http\Requests\v1\PageClaim\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Page\Support\Facade\PageClaim as PageClaimFacade;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Page\Http\Controllers\Api\v1\PageClaimAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'status' => ['required', 'numeric', new AllowInRule(PageClaimFacade::getAllowStatusId())],
        ];
    }
}
