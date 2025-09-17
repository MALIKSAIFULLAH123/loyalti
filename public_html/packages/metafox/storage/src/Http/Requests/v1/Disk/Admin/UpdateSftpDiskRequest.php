<?php

namespace MetaFox\Storage\Http\Requests\v1\Disk\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Storage\Http\Controllers\Api\v1\DiskAdminController::awsS3
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateSftpDiskRequest.
 */
class UpdateSftpDiskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'host'            => 'required|string',
            'port'            => 'sometimes|int|nullable',
            'timeout'         => 'sometimes|int|nullable',
            'maxTries'        => 'sometimes|int|nullable',
            'username'        => 'required|string',
            'password'        => 'sometimes|string|nullable',
            'root'            => 'sometimes|string|nullable',
            'hostFingerprint' => 'sometimes|string|nullable',
            'throw'           => 'sometimes|boolean|nullable',
            'useAgent'        => 'sometimes|boolean|nullable',
            'driver'          => 'required|string',
            'visibility'      => 'sometimes|string|nullable',
            'privateKey'      => 'sometimes|string|nullable',
            'url'             => 'required|string',
            'passphrase'      => 'sometimes|nullable|string',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        Arr::set($data, 'useAgent', (bool) Arr::get($data, 'useAgent', false));

        $data = $this->handlePort($data);

        $data = $this->handleTimeout($data);

        $data = $this->handleMaxTries($data);

        return $data;
    }

    protected function handleMaxTries(array $data): array
    {
        if (!Arr::has($data, 'maxTries')) {
            return $data;
        }

        $maxTries = Arr::get($data, 'maxTries');

        if (!is_numeric($maxTries)) {
            unset($data['maxTries']);

            return $data;
        }

        Arr::set($data, 'maxTries', (int) $data['maxTries']);

        return $data;
    }

    protected function handleTimeout(array $data): array
    {
        if (!Arr::has($data, 'timeout')) {
            return $data;
        }

        $timeout = Arr::get($data, 'timeout');

        if (!is_numeric($timeout)) {
            unset($data['timeout']);

            return $data;
        }

        Arr::set($data, 'timeout', (int) $data['timeout']);

        return $data;
    }

    protected function handlePort(array $data): array
    {
        if (!Arr::has($data, 'port')) {
            return $data;
        }

        $port = Arr::get($data, 'port');

        if (!is_numeric($port)) {
            unset($data['port']);

            return $data;
        }

        Arr::set($data, 'port', (int) $data['port']);

        return $data;
    }
}
