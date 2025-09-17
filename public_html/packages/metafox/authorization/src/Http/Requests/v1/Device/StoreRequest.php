<?php

namespace MetaFox\Authorization\Http\Requests\v1\Device;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Models\UserDevice;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Authorization\Http\Controllers\Api\v1\DeviceController::store
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
            'device_token'     => ['required', 'string'],
            'device_id'        => ['required', 'string'],
            'device_uid'       => ['sometimes', 'string', 'nullable'],
            'token_source'     => ['required', 'string'],
            'platform'         => ['sometimes', 'string', 'nullable'],
            'platform_version' => ['sometimes', 'string', 'nullable'],
            'extra'            => ['sometimes', 'array', 'nullable'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = Arr::add($data, 'platform', UserDevice::DEVICE_MOBILE_PLATFORM);
        $data = Arr::add($data, 'token_source', 'firebase');

        return $data;
    }
}
