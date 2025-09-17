<?php

namespace MetaFox\User\Http\Requests\v1\UserGender\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\User\Rules\CustomGenderRule;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'is_custom' => ['sometimes', 'numeric', 'nullable', new AllowInRule([0, 1])],
            'phrase'    => ['required', 'array', new TranslatableTextRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Default of is_custom is 1
        $data = Arr::add($data, 'is_custom', 1);

        Arr::set($data, 'phrase', Language::extractPhraseData('phrase', $data));

        return $data;
    }
}
