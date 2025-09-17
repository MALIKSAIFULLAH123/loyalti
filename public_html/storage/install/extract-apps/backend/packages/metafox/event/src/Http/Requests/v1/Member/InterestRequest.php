<?php

namespace MetaFox\Event\Http\Requests\v1\Member;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Event\Models\Member;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class IndexRequest.
 */
class InterestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'interest'    => ['required', 'numeric', new AllowInRule([
                Member::INTERESTED,
                Member::NOT_INTERESTED,
            ])],
            'invite_code' => ['sometimes', 'string'],
        ];
    }
}
