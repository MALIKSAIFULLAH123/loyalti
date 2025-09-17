<?php

namespace MetaFox\Event\Http\Requests\v1\Member;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Event\Support\Browse\Scopes\Member\ViewScope;
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
            'q'        => ['sometimes'],
            'event_id' => ['required', 'numeric', 'exists:events,id'],
            'view'     => ['sometimes', 'string', new AllowInRule(ViewScope::getAllowView())],
            'page'     => ['sometimes', 'numeric', 'min:1'],
            'limit'    => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['view'])) {
            $data['view'] = ViewScope::VIEW_INTERESTED;
        }

        if (!isset($data['q'])) {
            $data['q'] = '';
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        return $data;
    }
}
