<?php

namespace MetaFox\Search\Http\Requests\v1\Search;

use Illuminate\Support\Arr;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Search\Http\Controllers\Api\v1\SearchController::index;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 */
class IndexRequest extends GroupRequest
{
    public const DEFAULT_ITEM_PER_PAGE = 10;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->getStandardRules(), [
            'limit'          => ['sometimes', 'numeric', 'min:2'],
            'last_search_id' => ['sometimes', 'numeric', 'min:1'],
            'view'           => ['sometimes', 'string'],
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', self::DEFAULT_ITEM_PER_PAGE);
        }

        if (!Arr::has($data, 'last_search_id')) {
            Arr::set($data, 'last_search_id', 0);
        }

        if (!Arr::has($data, 'page')) {
            Arr::set($data, 'page', 1);
        }

        if (!Arr::has($data, 'view')) {
            Arr::set($data, 'view', Browse::VIEW_ALL);
        }

        return $data;
    }
}
