<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Activity\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Activity\Http\Controllers\Api\v1\FeedController::index;
 * stub: api_action_request.stub
 */

/**
 * Class UpdateSortRequest.
 */
class UpdateSortRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'sort'    => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['user_id'])) {
            $data['user_id'] = null;
        }

        if (!isset($data['sort'])) {
            $data['sort'] = null;
        }

        return $data;
    }
}
