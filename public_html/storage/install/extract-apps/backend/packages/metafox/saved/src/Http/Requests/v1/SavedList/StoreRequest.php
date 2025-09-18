<?php

namespace MetaFox\Saved\Http\Requests\v1\SavedList;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    use PrivacyRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxCollectionNameLength = Settings::get('saved.maximum_name_length', 64);

        return [
            'name'    => ['required', 'string', 'max:' . $maxCollectionNameLength],
            'privacy' => ['required', new PrivacyRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handlePrivacy($data);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        $maxCollectionNameLength = Settings::get('saved.maximum_name_length', 64);

        return [
            'name.max' => __p('saved::validation.collection_name_is_too_long_the_maximum_length_is_max', [
                'max' => $maxCollectionNameLength,
            ]),
        ];
    }
}
