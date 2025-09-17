<?php

namespace MetaFox\Event\Http\Requests\v1\Invite;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

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
        return [
            'event_id' => ['required', 'numeric', 'exists:events,id'],
            'accept'   => ['required', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
