<?php

namespace MetaFox\Profile\Http\Requests\v1\Section\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;
use MetaFox\Profile\Support\Facade\CustomField;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Profile\Http\Controllers\Api\v1\SectionAdminController::index
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
    public function rules()
    {
        return [
            'limit'        => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
            'page'         => ['sometimes', 'nullable', 'integer', 'min:1'],
            'title'        => ['sometimes', 'nullable', 'string'],
            'active'       => ['sometimes', 'numeric', 'nullable', new AllowInRule([0, 1])],
            'section_type' => [
                'string', new AllowInRule(CustomField::getAllowSectionType()),
            ],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['section_type'])) {
            $data['section_type'] = CustomFieldSupport::SECTION_TYPE_USER;
        }

        return $data;
    }
}
