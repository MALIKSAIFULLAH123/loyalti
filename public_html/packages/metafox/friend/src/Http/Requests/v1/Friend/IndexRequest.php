<?php

namespace MetaFox\Friend\Http\Requests\v1\Friend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Friend\Support\Browse\Scopes\Friend\SortScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;

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
            'q'                => ['sometimes', 'nullable', 'string'],
            'view'             => ['sometimes', 'string', 'in:mutual,latest,friend,profile,search'],
            'sort'             => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'        => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'when'             => ['sometimes', 'string', new AllowInRule(WhenScope::getAllowWhen())],
            'list_id'          => ['sometimes', 'numeric', 'exists:friend_lists,id'],
            'user_id'          => ['required_if:view,mutual,profile', 'numeric', 'exists:user_entities,id'],
            'page'             => ['sometimes', 'numeric', 'min:1'],
            'limit'            => ['sometimes', 'numeric', new PaginationLimitRule()],
            'owner_id'         => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'share_on_profile' => ['sometimes', new AllowInRule([0, 1])],
            'is_member_only'   => ['sometimes', 'boolean'],
            'is_featured'      => ['sometimes', 'numeric'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->assignDefault($data);

        return $this->assignOwnerId($data);
    }

    protected function assignOwnerId(array $data): array
    {
        if (Arr::has($data, 'owner_id')) {
            Arr::set($data, 'user_id', $data['owner_id']);
        }

        return $data;
    }

    protected function assignDefault(array $data): array
    {
        if (!isset($data['limit'])) {
            $data['limit'] = $this->getDefaultLimit();
        }

        if (!isset($data['view'])) {
            $data['view'] = '';
        }

        if (!isset($data['q'])) {
            $data['q'] = '';
        }

        if (!isset($data['list_id'])) {
            $data['list_id'] = 0;
        }

        if (!Arr::has($data, 'user_id')) {
            Arr::set($data, 'user_id', 0);
        }

        $isFeatured = Arr::get($data, 'is_featured');

        if (!$isFeatured) {
            $data['is_featured'] = null;
        }

        if (Arr::has($data, 'share_on_profile')) {
            Arr::set($data, 'share_on_profile', (bool) Arr::get($data, 'share_on_profile'));
        }

        return $data;
    }

    protected function getDefaultLimit(): int
    {
        return Pagination::DEFAULT_ITEM_PER_PAGE_SPECIAL_CASE;
    }
}
