<?php

namespace MetaFox\AntiSpamQuestion\Http\Requests\v1\Question\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\AntiSpamQuestion\Http\Controllers\Api\v1\QuestionAdminController::index
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
            'q'                 => ['nullable', 'string'],
            'is_active'         => ['nullable', 'integer', new AllowInRule([0, 1])],
            'is_case_sensitive' => ['nullable', 'integer', new AllowInRule([0, 1])],
            'created_from'      => ['sometimes', 'nullable', 'string'],
            'created_to'        => ['sometimes', 'nullable', 'string', 'after:created_from'],
            'page'              => ['nullable', 'integer'],
            'limit'             => ['nullable', 'integer'],
        ];
    }
}
