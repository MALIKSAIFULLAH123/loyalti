<?php

namespace MetaFox\User\Http\Requests\v1\ExportProcess\Admin;

use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\User\Http\Requests\v1\User\Admin\IndexRequest as FormRequest;
use MetaFox\User\Support\User;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\ExportProcessAdminController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest
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
            'q'          => ['sometimes', 'string', 'min:1'],
            'status'     => ['sometimes', 'string', new AllowInRule(Arr::pluck(User::allowedStatusExportOptions(), 'value'))],
            'process_id' => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_export_processes,id')],
            'sort'       => ['sometimes', 'string'],
            'sort_type'  => ['sometimes', 'string'],
            'page'       => ['sometimes', 'numeric', 'min:1'],
            'limit'      => ['sometimes', 'numeric', new PaginationLimitRule(20, 500)],
        ];
    }
}
