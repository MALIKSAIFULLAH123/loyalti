<?php

namespace MetaFox\Activity\Http\Requests\v1\Snooze;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Activity\Support\Facades\Snooze;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Activity\Http\Controllers\Api\v1\SnoozeController::index;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 */
class IndexRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'q'     => ['sometimes', 'nullable', 'string'],
            'type'  => ['sometimes', new AllowInRule(Snooze::getAllowedSnoozeTypes())],
            'page'  => ['sometimes', 'numeric', 'min:1'],
            'limit' => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }
}
