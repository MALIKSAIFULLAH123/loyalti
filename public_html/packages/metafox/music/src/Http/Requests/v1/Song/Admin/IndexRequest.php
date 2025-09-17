<?php

namespace MetaFox\Music\Http\Requests\v1\Song\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Music\Support\Browse\Scopes\Song\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Song\ViewAdminScope;
use MetaFox\Music\Support\Browse\Scopes\Song\ViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Music\Http\Controllers\Api\v1\SongAdminController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest
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
            'q'            => ['sometimes', 'nullable', 'string'],
            'view'         => ['sometimes', 'string', new AllowInRule(ViewAdminScope::getAllowView())],
            'sort'         => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'    => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'owner_name'   => ['sometimes', 'nullable', 'string'],
            'user_name'    => ['sometimes', 'nullable', 'string'],
            'created_from' => ['sometimes', 'nullable', 'string'],
            'created_to'   => ['sometimes', 'nullable', 'string', 'after:created_from'],
            'genre_id'     => ['sometimes', 'numeric', 'exists:music_genres,id'],
            'category_id'  => ['sometimes', 'integer', 'exists:music_genres,id'],
            'page'         => ['sometimes', 'numeric', 'min:1'],
            'limit'        => ['sometimes', 'numeric', new PaginationLimitRule(20, 500)],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!Arr::has($data, 'view')) {
            Arr::set($data, 'view', ViewScope::VIEW_DEFAULT);
        }

        if (!Arr::has($data, 'sort')) {
            Arr::set($data, 'sort', SortScope::SORT_DEFAULT);
        }

        if (!Arr::has($data, 'sort_type')) {
            Arr::set($data, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        }

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        }

        if (!Arr::has($data, 'genre_id')) {
            Arr::set($data, 'genre_id', 0);
        }

        $q = Arr::get($data, 'q');

        if (null === $q) {
            $q = MetaFoxConstant::EMPTY_STRING;
        }

        $q = trim($q);

        Arr::set($data, 'q', $q);

        /*
         * Support special case for mobile
         */
        if (Arr::has($data, 'category_id')) {
            Arr::set($data, 'genre_id', Arr::get($data, 'category_id'));
            unset($data['category_id']);
        }

        return $data;
    }
}
