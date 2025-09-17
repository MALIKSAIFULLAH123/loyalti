<?php

namespace MetaFox\Profile\Http\Requests\v1\Field\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Profile\Support\Facade\CustomField;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Profile\Http\Controllers\Api\v1\FieldAdminController::index
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
            'role_id'      => ['sometimes', 'nullable', 'integer', new AllowInRule(CustomField::getAllowedRole())],
            'title'        => ['sometimes', 'nullable', 'string'],
            'required'     => ['sometimes', 'numeric', 'nullable', new AllowInRule([0, 1])],
            'active'       => ['sometimes', 'numeric', 'nullable', new AllowInRule([0, 1])],
            'section_type' => ['sometimes', 'string', new AllowInRule(CustomField::getAllowSectionType())],
            'limit'        => ['sometimes', 'nullable', 'integer', new PaginationLimitRule(null, 500)],
            'page'         => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        return $data;
    }
}
