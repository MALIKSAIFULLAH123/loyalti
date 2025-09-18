<?php

namespace MetaFox\Group\Http\Requests\v1\Invite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Group\Support\Browse\Scopes\Invite\StatusScope;
use MetaFox\Group\Support\Browse\Scopes\Invite\ViewScope;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
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
            'group_id'     => ['sometimes', 'numeric', 'exists:groups,id'],
            'q'            => ['sometimes', 'nullable', 'string'],
            'sort'         => ['sometimes', 'nullable', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'    => ['sometimes', 'nullable', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'created_from' => ['sometimes', 'nullable', 'string'],
            'created_to'   => ['sometimes', 'nullable', 'string', 'after:created_from'],
            'view'         => ['sometimes', 'nullable', new AllowInRule(ViewScope::getAllowView())],
            'status'       => ['sometimes', 'nullable', new AllowInRule(StatusScope::getAllowStatus())],
            'page'         => ['sometimes', 'numeric', 'min:1'],
            'limit'        => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.16', '<')) {
            Arr::set($data, 'view', ViewScope::VIEW_MEMBERS);
            Arr::set($data, 'status', StatusScope::STATUS_PENDING);
            return $data;
        }

        if (!Arr::has($data, 'view') || empty(Arr::get($data, 'view'))) {
            Arr::set($data, 'view', ViewScope::VIEW_ALL);
        }

        return $data;
    }
}
