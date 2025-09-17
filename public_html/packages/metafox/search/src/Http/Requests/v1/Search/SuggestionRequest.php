<?php

namespace MetaFox\Search\Http\Requests\v1\Search;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use MetaFox\Platform\Traits\Helpers\InputCleanerTrait;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Search\Http\Controllers\Api\v1\SearchController::suggestion();
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class SearchSuggestionRequest.
 */
class SuggestionRequest extends FormRequest
{
    use InputCleanerTrait;

    public const SEARCH_SUGGESTION_LIMIT = 10;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'limit' => ['sometimes', 'numeric', 'min:10'],
            'q'     => ['sometimes', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data['is_hashtag_search'] = false;

        if (!isset($data['limit'])) {
            $data['limit'] = self::SEARCH_SUGGESTION_LIMIT;
        }

        if (isset($data['q'])) {
            if ('#' === $data['q'][0]) {
                $data['is_hashtag_search'] = true;
                $data['q'] = Str::substr($data['q'], 1);
            }

            $data['q'] = $this->cleanTitle($data['q']);
        }

        return $data;
    }
}
