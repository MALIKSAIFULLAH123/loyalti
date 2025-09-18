<?php

namespace MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\DurationScope;
use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\ViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\LiveStreaming\Http\Controllers\Api\v1\LiveVideoController::index
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
            'q'           => ['sometimes', 'nullable', 'string'],
            'view'        => ViewScope::rules(),
            'sort'        => SortScope::rules(),
            'sort_type'   => SortScope::sortTypes(),
            'when'        => WhenScope::rules(),
            'owner_id'    => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
            'user_id'     => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
            'page'        => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'       => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
            'duration'    => DurationScope::rules(),
            'streaming'   => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'is_featured' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['view'])) {
            $data['view'] = ViewScope::VIEW_DEFAULT;
        }

        if (!isset($data['sort'])) {
            $data['sort'] = SortScope::SORT_DEFAULT;
        }

        if (!isset($data['sort_type'])) {
            $data['sort_type'] = SortScope::SORT_TYPE_DEFAULT;
        }

        if (!isset($data['when'])) {
            $data['when'] = WhenScope::WHEN_DEFAULT;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['user_id']) && !isset($data['owner_id'])) {
            $data['user_id'] = 0;
        }

        if (isset($data['owner_id'])) {
            $data['user_id'] = $data['owner_id'];
        }

        $isSearching = Arr::has($data, 'q');

        if (!$isSearching) {
            Arr::set($data, 'q', MetaFoxConstant::EMPTY_STRING);

            return $data;
        }

        $q = Arr::get($data, 'q');

        if (null === $q) {
            $q = MetaFoxConstant::EMPTY_STRING;
        }

        $q = trim($q);

        Arr::set($data, 'q', $q);

        Arr::set($data, 'view', Browse::VIEW_SEARCH);

        if (Str::startsWith($q, '#')) {
            $tag = Str::of($q)
                ->replace('#', '')
                ->trim();

            Arr::set($data, 'tag', $tag);

            Arr::set($data, 'q', MetaFoxConstant::EMPTY_STRING);
        }

        return $data;
    }
}
