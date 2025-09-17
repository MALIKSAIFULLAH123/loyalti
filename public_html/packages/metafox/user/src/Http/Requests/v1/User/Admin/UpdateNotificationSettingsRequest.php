<?php

namespace MetaFox\User\Http\Requests\v1\User\Admin;

use Illuminate\Support\Arr;
use MetaFox\User\Http\Requests\v1\User\UpdateRequest as UserUpdateRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserAdminController::updateNotificationSettings;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateNotificationSettingsRequest.
 */
class UpdateNotificationSettingsRequest extends UserUpdateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notification' => ['sometimes', 'nullable', 'array'],
        ];
    }

    /**
     * @param  string               $key
     * @param  mixed                $default
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $this->handleNotification($data);

        return $data;
    }

    private function handleNotification(array &$data): void
    {
        $result = [];

        foreach (Arr::get($data, 'notification') as $channel => $types) {
            foreach ($types as $type => $values) {
                foreach ($values as $name => $value) {
                    $result[] = [
                        'channel' => $channel,
                        'value'   => $value,
                        $type     => $name,
                    ];
                }
            }
        }

        Arr::set($data, 'notification', $result);
    }
}
