<?php

namespace MetaFox\Music\Http\Requests\v1\Playlist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Music\Support\Browse\Scopes\Playlist\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Playlist\ViewScope;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Music\Http\Controllers\Api\v1\PlaylistController::index;
 * stub: api_action_request.stub
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
            'q'           => ['sometimes', 'nullable', 'string'],
            'view'        => ['sometimes', 'string', new AllowInRule(ViewScope::getAllowView())],
            'sort'        => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'   => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'when'        => ['sometimes', 'string', new AllowInRule(WhenScope::getAllowWhen())],
            'user_id'     => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'page'        => ['sometimes', 'numeric', 'min:1'],
            'limit'       => ['sometimes', 'numeric', new PaginationLimitRule()],
            'genre_id'    => ['sometimes', 'integer'],
            'category_id' => ['sometimes', 'integer'],
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

        if (!Arr::has($data, 'when')) {
            Arr::set($data, 'when', WhenScope::WHEN_DEFAULT);
        }

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        }

        if (!Arr::has($data, 'user_id')) {
            Arr::set($data, 'user_id', 0);
        }

        $q = Arr::get($data, 'q');

        if (null === $q) {
            $q = MetaFoxConstant::EMPTY_STRING;
        }

        $q = trim($q);

        if (Str::startsWith($q, '#')) {
            $tag = trim($q, '#');

            Arr::set($data, 'tag', $tag);

            $q = MetaFoxConstant::EMPTY_STRING;
        }

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
