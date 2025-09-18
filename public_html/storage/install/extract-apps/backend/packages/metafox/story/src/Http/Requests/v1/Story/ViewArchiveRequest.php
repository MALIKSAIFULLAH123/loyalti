<?php

namespace MetaFox\Story\Http\Requests\v1\Story;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Story\Http\Controllers\Api\v1\StoryController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ViewArchiveRequest.
 */
class ViewArchiveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user_id'   => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
            'story_id'  => ['sometimes', 'nullable', 'numeric', 'exists:stories,id'],
            'page'      => ['sometimes', 'numeric', 'min:1'],
            'date'      => ['sometimes', 'date'],
            'from_date' => ['sometimes', 'date'],
            'to_date'   => ['sometimes', 'date'],
            'limit'     => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (Arr::has($data, 'from_date')) {
            $date     = Carbon::make(Arr::get($data, 'from_date'));
            $fromDate = Carbon::make($date)->setTimezone('UTC');

            Arr::set($data, 'from_date', $fromDate);
        }

        if (Arr::has($data, 'to_date')) {
            $date   = Carbon::make(Arr::get($data, 'to_date'));
            $toDate = Carbon::make($date)->setTimezone('UTC');

            Arr::set($data, 'to_date', $toDate);
        }

        return $data;
    }
}
