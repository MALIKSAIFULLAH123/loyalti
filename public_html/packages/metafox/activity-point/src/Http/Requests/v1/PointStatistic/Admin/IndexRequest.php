<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\PointStatistic\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Support\Browse\Scopes\PointStatistic\SortScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\ActivityPoint\Http\Controllers\Api\v1\PointStatisticAdminController::index
 * stub: /packages/requests/api_action_request.stub
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
            'q'         => ['sometimes', 'nullable'],
            'sort'      => ['sometimes', new AllowInRule(SortScope::getAllowSort())],
            'sort_type' => ['sometimes', new AllowInRule([Browse::SORT_TYPE_ASC, Browse::SORT_TYPE_DESC])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = Arr::add($data, 'sort', SortScope::SORT_DEFAULT);
        $data = Arr::add($data, 'sort_type', SortScope::SORT_TYPE_DEFAULT);

        return $data;
    }
}
