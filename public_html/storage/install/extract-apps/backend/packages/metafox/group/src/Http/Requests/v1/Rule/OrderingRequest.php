<?php

namespace MetaFox\Group\Http\Requests\v1\Rule;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Http\Requests\v1\OrderingRequest as PlatformOrderingRequest;

/**
 * Class OrderingRequest.
 */
class OrderingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new PlatformOrderingRequest())->rules(),
            ['group_id' => ['required', 'numeric', 'exists:groups,id']]
        );
    }
}
