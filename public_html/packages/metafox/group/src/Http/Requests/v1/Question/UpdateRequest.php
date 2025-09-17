<?php

namespace MetaFox\Group\Http\Requests\v1\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Rules\QuestionOptionsRule;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
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
            'question'         => ['sometimes', 'string'],
            'type_id'          => ['sometimes', 'numeric', new AllowInRule($types)],
            'options'          => [
                'required_unless:type_id,' . Question::TYPE_TEXT,
                'array',
                new QuestionOptionsRule((int) $this->input('type_id', Question::TYPE_TEXT)),
            ],
            'options.*'        => ['required_unless:type_id,' . Question::TYPE_TEXT, 'sometimes', 'array'],
            'options.*.id'     => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:group_question_fields,id')],
            'options.*.status' => ['sometimes', 'string'],
            'options.*.title'  => ['required_unless:options.*.status,remove', 'string'],
        ];
    }

    /**
     * @param mixed|null $key
     * @param mixed|null $default
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data            = parent::validated();
        $data            = Arr::add($data, 'options', []);
        $options         = Arr::get($data, 'options');
        $data['options'] = collect($options)->groupBy('status')->toArray();

        return $data;
    }

    public function messages(): array
    {
        return [
            'question.string'                 => __p('group::phrase.question_is_a_required_field'),
            'options.required_unless'         => __p('group::phrase.question_requires_at_least_two_option'),
            'options.*.title.required_unless' => __p('group::phrase.answer_title_is_a_required_field'),
        ];
    }
}
