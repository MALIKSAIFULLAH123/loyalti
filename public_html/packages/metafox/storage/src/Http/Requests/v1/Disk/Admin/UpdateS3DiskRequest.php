<?php

namespace MetaFox\Storage\Http\Requests\v1\Disk\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
 * Class UpdateS3DiskRequest.
 */
class UpdateS3DiskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'key'                     => 'required|string',
            'secret'                  => 'required|string',
            'bucket'                  => 'required|string',
            'region'                  => 'required|string',
            'url'                     => 'sometimes|string|nullable',
            'endpoint'                => 'sometimes|string|nullable',
            'use_path_style_endpoint' => 'sometimes|boolean|nullable',
            'throw'                   => 'sometimes|boolean|nullable',
            'driver'     => 'required|string',
        ];
    }
}
