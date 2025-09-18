<?php

namespace MetaFox\Group\Http\Requests\v1\Rule;

use MetaFox\Group\Support\Facades\GroupRule;
use MetaFox\Platform\MetaFoxConstant;

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
        $maxDescriptionLength = GroupRule::getDescriptionMaxLength();
        $minDescriptionLength = GroupRule::getDescriptionMinLength();

        return [
            'title'       => ['required', 'string', 'between:' . MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH . ',' . MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH],
            'description' => ['nullable', 'string', 'between:' . $minDescriptionLength . ',' . $maxDescriptionLength],
        ];
    }
}
