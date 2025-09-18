<?php

namespace MetaFox\Group\Http\Requests\v1\ExampleRule\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\Rules\AllowInRule;

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
            'title'       => ['required', 'array', new TranslatableTextRule()],
            'description' => ['required', 'array', new TranslatableTextRule()],
            'is_active'   => ['sometimes', 'nullable', new AllowInRule([0, 1])],
        ];
    }

    /**
     * @param        $key
     * @param        $default
     * @return mixed
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);
        Arr::set($data, 'title', Language::extractPhraseData('title', $data));
        Arr::set($data, 'description', Language::extractPhraseData('description', $data));

        return $data;
    }
}
