<?php

namespace MetaFox\Page\Http\Requests\v1\PageClaim;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Page\Http\Controllers\Api\v1\PageClaimController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['sometimes', 'string', 'nullable'],
            'page_id' => ['sometimes', 'numeric', 'exists:pages,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['message'])) {
            $data['message'] = '';
        }

        return $data;
    }
}
