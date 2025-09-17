<?php

namespace MetaFox\Notification\Http\Requests\v1\Type\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Notification\Repositories\NotificationChannelRepositoryInterface;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Notification\Http\Controllers\Api\v1\TypeAdminController::toggleChannel;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ToggleChannelRequest.
 */
class ToggleChannelRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'channel' => $this->route('channel'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id'      => ['required', 'int', 'exists:notification_types,id'],
            'channel' => ['required', 'string', 'exists:notification_channels,name'],
            'active'  => ['required', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
