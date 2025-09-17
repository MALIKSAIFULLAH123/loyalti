<?php

namespace MetaFox\Storage\Http\Requests\v1\Disk\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Storage\Http\Controllers\Api\v1\DiskAdminController::store
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
            'name'   => ['required', 'string', new CaseInsensitiveUnique('storage_disks', 'name')],
            'label' => ['required', 'string', new CaseInsensitiveUnique('storage_disks', 'label')],
            'target'  => 'required|string',
        ];
    }
}
