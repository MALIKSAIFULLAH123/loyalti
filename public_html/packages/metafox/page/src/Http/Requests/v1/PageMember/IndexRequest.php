<?php

namespace MetaFox\Page\Http\Requests\v1\PageMember;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Page\Support\Browse\Scopes\PageMember\ViewScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
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
            'q'                => ['sometimes', 'string', 'nullable'],
            'page_id'          => ['required', 'numeric', 'exists:pages,id'],
            'page'             => ['sometimes', 'numeric', 'min:1'],
            'limit'            => ['sometimes', 'numeric', new PaginationLimitRule()],
            'view'             => ['sometimes', 'string', new AllowInRule(ViewScope::getAllowView())],
            'not_invite_role'  => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'excluded_user_id' => ['sometimes', 'numeric', 'exists:user_entities,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['view'])) {
            $data['view'] = ViewScope::VIEW_MEMBER;
        }

        if (!isset($data['q'])) {
            $data['q'] = '';
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['excluded_user_id'])) {
            $data['excluded_user_id'] = null;
        }

        return $data;
    }
}
