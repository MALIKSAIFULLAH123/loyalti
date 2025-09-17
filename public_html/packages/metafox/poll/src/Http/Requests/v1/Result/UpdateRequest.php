<?php

namespace MetaFox\Poll\Http\Requests\v1\Result;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Poll\Rules\PollResultAnswersRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Poll\Http\Controllers\Api\v1\ResultController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pollId = (int) ($this->route('poll_result'));

        return [
            'answers' => ['sometimes', 'array', 'min:1', new PollResultAnswersRule($pollId)],
        ];
    }
}
