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
 * Class UpdateAliasDiskRequest.
 */
class UpdateAliasDiskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'target' => 'required|string',
            'driver' => 'required|string',
        ];
    }
}
