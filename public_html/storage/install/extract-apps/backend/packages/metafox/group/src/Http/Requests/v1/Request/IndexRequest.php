<?php

namespace MetaFox\Group\Http\Requests\v1\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Group\Support\Browse\Scopes\GroupMember\ViewScope;
use MetaFox\Group\Support\Browse\Scopes\Request\StatusScope;
use MetaFox\Platform\MetaFox;
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
            'group_id'   => ['required', 'numeric', 'exists:groups,id'],
            'q'          => ['sometimes', 'nullable', 'string'],
            'view'       => ['sometimes', 'nullable', 'string', new AllowInRule(ViewScope::getAllowView())],
            'status'     => ['sometimes', 'nullable', 'numeric', new AllowInRule(StatusScope::getAllowStatus())],
            'start_date' => ['sometimes', 'nullable', 'string'],
            'end_date'   => ['sometimes', 'nullable', 'string', 'after:start_date'],
            'page'       => ['sometimes', 'numeric', 'min:1'],
            'limit'      => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        return $this->handleDataForMobileOldVersion($data);
    }

    /**
     * @param array $data
     * @return array
     */
    private function handleDataForMobileOldVersion(array $data): array
    {
        if (Arr::get($data, 'status')) {
            return $data;
        }

        if (!MetaFox::isMobile()) {
            return $data;
        }

        if (!version_compare(MetaFox::getApiVersion(), 'v1.10', '<')) {
            return $data;
        }

        Arr::set($data, 'status', StatusScope::STATUS_PENDING);

        return $data;
    }
}
