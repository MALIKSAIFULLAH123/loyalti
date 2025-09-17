<?php

namespace MetaFox\GettingStarted\Http\Requests\v1\TodoList;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

class MarkRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'todo_list_id'    => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:gettingstarted_todo_lists,id')],
            'todo_list_ids'   => ['sometimes', 'array'],
            'todo_list_ids.*' => ['numeric', new ExistIfGreaterThanZero('exists:gettingstarted_todo_lists,id')],
        ];
    }
}
