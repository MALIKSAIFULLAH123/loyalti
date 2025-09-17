<?php

namespace MetaFox\Photo\Http\Requests\v1\Photo;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class MakeCoverRequest.
 */
class MakeCoverRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id'  => ['sometimes', 'int', 'exists:user_entities,id'],
            'position' => ['sometimes', 'string'],
        ];
    }

    /**
     * @throws AuthenticationException
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        if (!isset($data['user_id'])) {
            $data['user_id'] = user()->entityId();
        }
        return $data;
    }
}
