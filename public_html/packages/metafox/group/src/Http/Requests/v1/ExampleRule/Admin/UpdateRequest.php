<?php

namespace MetaFox\Group\Http\Requests\v1\ExampleRule\Admin;

use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends StoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'array', new TranslatableTextRule()],
            'description' => ['sometimes', 'array', new TranslatableTextRule()],
            'is_active'   => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
