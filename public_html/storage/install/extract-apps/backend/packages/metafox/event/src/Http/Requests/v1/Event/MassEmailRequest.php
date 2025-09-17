<?php

namespace MetaFox\Event\Http\Requests\v1\Event;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Event\Http\Controllers\Api\v1\EventController::massEmail();
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class MassEmailRequest.
 */
class MassEmailRequest extends FormRequest
{
    use PrivacyRequestTrait;
    use AttachmentRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'subject' => ['required', 'string'],
            'text'    => ['required', 'string'],
        ];
    }

    /**
     * @return array<string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        return $data;
    }
}
