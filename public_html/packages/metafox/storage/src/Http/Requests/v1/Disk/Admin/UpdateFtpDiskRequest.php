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
 * Class UpdateFtpDiskRequest.
 */
class UpdateFtpDiskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'host'       => 'required|string',
            'port'       => 'sometimes|nullable|int',
            'timeout'    => 'sometimes|nullable|int',
            'username'   => 'required|string',
            'password'   => 'required|string',
            'root'       => 'required|string',
            'url'        => 'required|string',
            'throw'      => 'sometimes|boolean|nullable',
            'passive'    => 'sometimes|boolean|nullable',
            'ssl'        => 'sometimes|boolean|nullable',
            'driver'     => 'required|string',
            'visibility' => 'sometimes|string',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handlePort($data);

        $data = $this->handleTimeout($data);

        $data = $this->handleSSL($data);

        $data = $this->handlePassive($data);

        $data = array_merge($data, [
            'directory_visibility' => 'public',
            'selectable'           => true,
            'ignorePassiveAddress' => true, //Notice: This flag is using to make sure working with FTP Passive mode if FTP server is wrong config for pasv_adddress
        ]);

        return $data;
    }

    protected function handlePassive(array $data): array
    {
        if (!Arr::has($data, 'passive')) {
            return $data;
        }

        $passive = Arr::get($data, 'passive');

        if (!is_numeric($passive) && !is_bool($passive)) {
            unset($data['passive']);

            return $data;
        }

        Arr::set($data, 'passive', (bool) $data['passive']);

        return $data;
    }

    protected function handleSSL(array $data): array
    {
        if (!Arr::has($data, 'ssl')) {
            return $data;
        }

        $ssl = Arr::get($data, 'ssl');

        if (!is_numeric($ssl) && !is_bool($ssl)) {
            unset($data['ssl']);

            return $data;
        }

        Arr::set($data, 'ssl', (bool) $data['ssl']);

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
}
