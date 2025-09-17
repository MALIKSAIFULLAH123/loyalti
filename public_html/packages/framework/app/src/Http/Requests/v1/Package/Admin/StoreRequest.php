<?php

namespace MetaFox\App\Http\Requests\v1\Package\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\PackageManager;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\App\Http\Controllers\Api\v1\PackageAdminController::store
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
            '--name'     => ['required', 'string'],
            '--vendor'   => ['required', 'string'],
            '--homepage' => ['required', 'string'],
            '--author'   => ['required', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data['package'] = PackageManager::normalizePackageName($data['--vendor'], $data['--name']);

        return $data;
    }
}
