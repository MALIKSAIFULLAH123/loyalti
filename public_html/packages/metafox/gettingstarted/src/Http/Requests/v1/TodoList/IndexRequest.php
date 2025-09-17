<?php

namespace MetaFox\GettingStarted\Http\Requests\v1\TodoList;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\GettingStarted\Support\Helper;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * Class IndexRequest.
 *
 * query parameters
 * @queryParam limit integer The items to return. Example: 10
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
        $resolutions = [MetaFoxConstant::RESOLUTION_WEB, MetaFoxConstant::RESOLUTION_MOBILE];

        return [
            'limit'      => ['sometimes', 'numeric', new PaginationLimitRule()],
            'page'       => ['sometimes', 'numeric'],
            'resolution' => ['sometimes', 'string', new AllowInRule($resolutions)],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['limit'])) {
            $data['limit'] = Helper::MAX_ITEMS;
        }

        if (!isset($data['resolution'])) {
            $data['resolution'] = MetaFox::getResolution();
        }

        return $data;
    }
}
