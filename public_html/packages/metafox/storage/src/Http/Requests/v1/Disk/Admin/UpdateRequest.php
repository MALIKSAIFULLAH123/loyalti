<?php

namespace MetaFox\Storage\Http\Requests\v1\Disk\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Storage\Http\Controllers\Api\v1\DiskAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = $this->route('disk');

        return [
            'name'   => ['required', 'string', new CaseInsensitiveUnique('storage_disks', 'name', $id)],
            'label'  => ['required', 'string', new CaseInsensitiveUnique('storage_disks', 'label', $id)],
            'target' => 'required|string',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = Arr::add($data, 'target', 'public');

        return $data;
    }
}
