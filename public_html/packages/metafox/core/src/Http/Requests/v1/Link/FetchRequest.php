<?php

namespace MetaFox\Core\Http\Requests\v1\Link;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Core\Support\Output;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Core\Http\Controllers\Api\v1\LinkController::fetch;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class FetchRequest.
 */
class FetchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'link'     => ['required'],
            'owner_id' => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $linkText = Arr::get($data, 'link') ?: '';

        $validator = Validator::make(['link' => $this->extractUrlString($linkText)], ['link' => ['required']]);

        return array_merge($data, $validator->validated());
    }

    protected function extractUrlString(string $linkText): ?string
    {
        /**
         * Scenarios:
         *
         * 1. A text provided without any valid links => fail it.
         * 2. A text contains multiple links => get the last link in the list
         */
        $matches = [];

        preg_match_all(Output::URL_REGEX, $linkText, $matches);

        return count($matches) > 0 ? end($matches[0]) : null;
    }
}
