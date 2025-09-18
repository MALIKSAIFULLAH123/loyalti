<?php

namespace MetaFox\Video\Http\Requests\v1\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Video\Support\Browse\Scopes\Video\SortScope;
use MetaFox\Video\Support\Browse\Scopes\Video\ViewScope;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Video\Http\Controllers\Api\v1\VideoController::index;
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
            'category_id' => ['sometimes', 'numeric', 'exists:video_categories,id'],
            'owner_id'    => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
            'user_id'     => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'page'        => ['sometimes', 'numeric', 'min:1'],
            'limit'       => ['sometimes', 'numeric', new PaginationLimitRule()],
            'is_featured' => ['sometimes', 'numeric'],
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

        if (!isset($data['category_id'])) {
            $data['category_id'] = 0;
        }

        if (!isset($data['user_id']) && !isset($data['owner_id'])) {
            $data['user_id'] = 0;
        }

        if (isset($data['owner_id'])) {
            $data['user_id'] = $data['owner_id'];
        }

        $isFeatured = Arr::get($data, 'is_featured');
        if (!$isFeatured) {
            $data['is_featured'] = null;
        }

        $q = Arr::get($data, 'q');

        if (null === $q) {
            $q = MetaFoxConstant::EMPTY_STRING;
        }

        $q = trim($q);

        if (Str::startsWith($q, '#')) {
            $data['tag'] = Str::substr($q, 1);

            $q   = MetaFoxConstant::EMPTY_STRING;
        }

        Arr::set($data, 'q', $q);

        return $data;
    }
}
