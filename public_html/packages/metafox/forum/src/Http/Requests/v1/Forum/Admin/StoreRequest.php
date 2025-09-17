<?php

namespace MetaFox\Forum\Http\Requests\v1\Forum\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\Rules\AllowInRule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'       => ['required', 'array', new TranslatableTextRule()],
            'description' => ['nullable', 'array', new TranslatableTextRule(true)],
            'parent_id'   => ['nullable', 'numeric', 'min:0'],
            'is_closed'   => ['required', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        Arr::set($data, 'title', Language::extractPhraseData('title', $data));
        Arr::set($data, 'description', Language::extractPhraseData('description', $data));

        return $data;
    }

    public function messages()
    {
        return [
            'title.required' => __p('core::phrase.title_is_a_required_field'),
            'title.string'   => __p('core::phrase.title_is_a_required_field'),
            'title.max'      => __p('forum::validation.admincp.maximum_name_length', [
                'number' => $this->getMaxTitleLength(),
            ]),
        ];
    }

    protected function getMaxTitleLength(): int
    {
        return ForumSupport::MAX_FORUM_TITLE_LEMGTH;
    }
}
