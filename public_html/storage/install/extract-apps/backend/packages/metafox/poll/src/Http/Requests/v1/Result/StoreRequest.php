<?php

namespace MetaFox\Poll\Http\Requests\v1\Result;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Poll\Rules\PollResultAnswersRule;
use MetaFox\Poll\Rules\PollResultPollIdRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Poll\Http\Controllers\Api\v1\ResultController::store;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pollId = $this->input('poll_id', 0);

        return [
            'poll_id' => ['required', 'numeric', 'min:1', new PollResultPollIdRule()],
            'answers' => ['required', 'array', 'min:1', new PollResultAnswersRule((int) $pollId)],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();
        $data['answers'] = collect($data['answers'])->unique()->values()->toArray();

        return $data;
    }
}
