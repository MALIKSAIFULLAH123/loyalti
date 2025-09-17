<?php

namespace MetaFox\Menu\Http\Requests\v1\MenuItem\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\HexColorRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Core\Http\Controllers\Api\v1\MenuItemAdminController::store;
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
            'label'       => ['required', 'string'],
            'menu'        => ['required', 'string', 'exists:core_menus,name'],
            'module_id'   => ['required', 'string'],
            'parent_name' => ['sometimes'],
            'resolution'  => ['sometimes', 'string', 'nullable'],
            'icon'        => ['sometimes'],
            'iconColor'   => ['sometimes', 'nullable', 'string', new HexColorRule()],
            'as'          => ['sometimes'],
            'to'          => ['sometimes'],
            'value'       => ['sometimes'],
            'sub_info'    => ['sometimes', 'string', 'nullable'],
            'ordering'    => ['sometimes'],
            'is_active'   => ['sometimes', 'numeric'],
            'is_custom'   => ['sometimes', 'numeric'],
            'showWhen'    => ['sometimes', 'string', 'nullable', 'JSON'],
            'enableWhen'  => ['sometimes', 'string', 'nullable', 'JSON'],
        ];
    }

    /**
     * @param string $key
     * @param mixed  $default
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $moduleId = Arr::get($data, 'module_id');

        $package = app('core.packages')->getPackageByAlias($moduleId);

        $data['package_id'] = $package->name;

        $data       = Arr::add($data, 'ordering', 1);
        $data       = Arr::add($data, 'is_custom', 1);
        $showWhen   = json_decode(Arr::get($data, 'showWhen', '[]'), 1);
        $enableWhen = json_decode(Arr::get($data, 'enableWhen', '[]'), 1);
        $subInfo    =  Arr::get($data, 'sub_info');

        Arr::set($data, 'showWhen', $showWhen ?? '');
        Arr::set($data, 'enableWhen', $enableWhen ?? '');
        Arr::set($data, 'subInfo', $subInfo);

        return $data;
    }

    public function messages()
    {
        return [
            'showWhen.j_s_o_n'   => __p('core::validation.json'),
            'enableWhen.j_s_o_n' => __p('core::validation.json'),
        ];
    }
}
