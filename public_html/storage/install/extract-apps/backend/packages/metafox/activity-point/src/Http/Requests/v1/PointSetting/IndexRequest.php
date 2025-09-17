<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\PointSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\ActivityPoint\Http\Controllers\Api\v1\PointSettingController::index
 */

/**
 * Class IndexRequest.
 */
class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sort'      => SortScope::rules(),
            'sort_type' => SortScope::sortTypes(),
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!Arr::has($data, 'sort')) {
            $data['sort'] = SortScope::SORT_DEFAULT;
        }

        if (!Arr::has($data, 'sort_type')) {
            $data['sort_type'] = SortScope::SORT_TYPE_DEFAULT;
        }

        return $data;
    }
}
