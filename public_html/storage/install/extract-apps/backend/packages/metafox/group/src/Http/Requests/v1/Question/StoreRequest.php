<?php

namespace MetaFox\Group\Http\Requests\v1\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Rules\QuestionOptionsRule;
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
        $types = [Question::TYPE_TEXT, Question::TYPE_SELECT, Question::TYPE_MULTI_SELECT];

        return [
            'group_id'         => ['required', 'numeric', 'exists:groups,id'],
            'question'         => ['required', 'string'],
            'type_id'          => ['required', 'numeric', new AllowInRule($types)],
            'options'          => [
                'required_unless:type_id,' . Question::TYPE_TEXT,
                'array',
                new QuestionOptionsRule((int) $this->input('type_id', Question::TYPE_TEXT)),
            ],
            'options.*'        => ['required_unless:type_id,' . Question::TYPE_TEXT, 'sometimes', 'array'],
            'options.*.title'  => ['required_unless:type_id,' . Question::TYPE_TEXT, 'sometimes', 'nullable', 'string'],
            'options.*.status' => ['sometimes', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (Arr::get($data, 'type_id') == Question::TYPE_TEXT) {
            Arr::forget($data, 'options');

            return $data;
        }

        $data            = Arr::add($data, 'options', []);
        $options         = Arr::get($data, 'options');
        $data['options'] = collect($options)->groupBy('status')->toArray();

        return $data;
    }

    public function messages(): array
    {
        return [
            'question.required'               => __p('group::phrase.question_is_a_required_field'),
            'question.string'                 => __p('group::phrase.question_is_a_required_field'),
            'options.required_unless'         => __p('group::phrase.question_requires_at_least_two_option'),
            'options.*.title.required_unless' => __p('group::phrase.answer_title_is_a_required_field'),
        ];
    }
}
