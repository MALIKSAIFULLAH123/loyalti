<?php

namespace MetaFox\User\Http\Requests\v1\User\Admin;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class BatchResendVerificationRequestBatchResendVerificationRequest.
 */
class BatchResendVerificationRequest extends ResendVerificationRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'id'   => ['required', 'array'],
            'id.*' => ['sometimes', 'numeric', 'exists:user_entities,id'],
        ]);
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated();
    }
}
