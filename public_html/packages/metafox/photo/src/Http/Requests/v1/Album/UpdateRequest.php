<?php

namespace MetaFox\Photo\Http\Requests\v1\Album;

use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\PrivacyRule;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends StoreRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        Arr::set($rules, 'privacy', ['sometimes', new PrivacyRule([
            'validate_privacy_list' => false,
        ])]);

        return $rules;
    }
}
