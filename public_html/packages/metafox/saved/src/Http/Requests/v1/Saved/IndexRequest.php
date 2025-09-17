<?php

namespace MetaFox\Saved\Http\Requests\v1\Saved;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Saved\Support\Browse\Scopes\Saved\OpenStatusScope;

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
            'q'             => ['sometimes', 'nullable', 'string'],
            'collection_id' => ['sometimes', 'numeric', 'exists:saved_lists,id'],
            'sort_type'     => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'open'          => ['sometimes', 'string', new AllowInRule(OpenStatusScope::getAllowStatuses())],
            'when'          => ['sometimes', 'string', new AllowInRule(WhenScope::getAllowWhen())],
            'type'          => ['sometimes', 'string'],
            'page'          => ['sometimes', 'numeric', 'min:1'],
            'limit'         => ['sometimes', 'numeric', 'min:10'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['sort_type'])) {
            $data['sort_type'] = SortScope::SORT_TYPE_DEFAULT;
        }

        if (!isset($data['open'])) {
            $data['open'] = 'all';
        }

        if (!isset($data['when'])) {
            $data['when'] = WhenScope::WHEN_DEFAULT;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['collection_id'])) {
            $data['collection_id'] = 0;
        }

        if (!isset($data['type'])) {
            $data['type'] = '';
        }

        if (!isset($data['q'])) {
            $data['q'] = '';
        }

        if (Str::startsWith($data['q'], '#')) {
            $data['tag'] = Str::substr($data['q'], 1);
            $data['q']   = MetaFoxConstant::EMPTY_STRING;
        }

        return $data;
    }
}
