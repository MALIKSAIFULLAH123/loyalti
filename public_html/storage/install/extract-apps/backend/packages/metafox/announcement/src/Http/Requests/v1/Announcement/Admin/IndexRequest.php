<?php

namespace MetaFox\Announcement\Http\Requests\v1\Announcement\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Announcement\Support\Facade\Announcement;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Announcement\Http\Controllers\Api\v1\AnnouncementAdminController::index
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
    public function rules(): array
    {
        return [
            'limit'        => ['sometimes', 'numeric', new PaginationLimitRule()],
            'q'            => ['sometimes', 'string', 'nullable'],
            'start_from'   => ['sometimes', 'date', 'nullable'],
            'start_to'     => ['sometimes', 'date', 'nullable'],
            'created_from' => ['sometimes', 'date', 'nullable'],
            'created_to'   => ['sometimes', 'date', 'nullable'],
            'style'        => ['sometimes', 'nullable', 'integer', new ExistIfGreaterThanZero('exists:announcement_styles,id')],
            'role_id'      => ['sometimes', 'nullable', 'integer', new AllowInRule(Announcement::getAllowedRole())],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!array_key_exists('limit', $data)) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!array_key_exists('q', $data)) {
            $data['q'] = MetaFoxConstant::EMPTY_STRING;
        }

        if (Arr::has($data, 'start_from')) {
            $data['start_from'] = Carbon::parse($data['start_from']);
        }

        if (Arr::has($data, 'start_to')) {
            $data['start_to'] = Carbon::parse($data['start_to'])->addDay()->subSecond();
        }

        if (Arr::has($data, 'created_from')) {
            $data['created_from'] = Carbon::parse($data['created_from']);
        }

        if (Arr::has($data, 'created_to')) {
            $data['created_to'] = Carbon::parse($data['created_to'])->addDay()->subSecond();
        }

        return $data;
    }
}
