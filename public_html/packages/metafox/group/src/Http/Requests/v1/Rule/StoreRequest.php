<?php

namespace MetaFox\Group\Http\Requests\v1\Rule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Group\Support\Facades\GroupRule;
use MetaFox\Platform\MetaFoxConstant;

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
        $maxDescriptionLength = GroupRule::getDescriptionMaxLength();
        $minDescriptionLength = GroupRule::getDescriptionMinLength();

        return [
            'group_id'    => ['required', 'numeric', 'exists:groups,id'],
            'title'       => ['required', 'string', 'between:' . MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH . ',' . MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH],
            'description' => ['nullable', 'string', 'between:' . $minDescriptionLength . ',' . $maxDescriptionLength],
        ];
    }

    protected function getDescriptionLengthMessage(): string
    {
        $maxDescriptionLength = GroupRule::getDescriptionMaxLength();
        $minDescriptionLength = GroupRule::getDescriptionMinLength();

        return __p('core::phrase.number_of_characters_of_description_must_be_between_min_and_max', [
            'min' => $minDescriptionLength,
            'max' => $maxDescriptionLength,
        ]);
    }

    protected function getTitleLengthMessage(): string
    {
        return __p('core::phrase.number_of_characters_of_title_must_be_between_min_and_max', [
            'min' => MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH,
            'max' => MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH,
        ]);
    }

    public function messages(): array
    {
        $titleLengthMessage = $this->getTitleLengthMessage();
        $descriptionLengthMessage = $this->getDescriptionLengthMessage();

        return [
            'title.required' => __p('core::phrase.title_is_a_required_field'),
            'title.string' => $titleLengthMessage,
            'title.between' => $titleLengthMessage,
            'description.string' => $descriptionLengthMessage,
            'description.between' => $descriptionLengthMessage,
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!array_key_exists('description', $data)) {
            Arr::set($data, 'description', '');
        }

        $data['description'] = trim($data['description']);

        return $data;
    }
}
