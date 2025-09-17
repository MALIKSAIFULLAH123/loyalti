<?php

namespace MetaFox\Comment\Http\Requests\v1\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class HideRequest.
 */
class HideRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'comment_id' => ['required', 'numeric', 'exists:comments,id'],
            'is_hidden'  => ['required', new AllowInRule([0, 1])],
            'is_global'  => ['sometimes', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        Arr::set($data, 'is_hidden', (bool) Arr::get($data, 'is_hidden'));

        Arr::set($data, 'is_global', (bool) Arr::get($data, 'is_global', false));

        return $data;
    }
}
