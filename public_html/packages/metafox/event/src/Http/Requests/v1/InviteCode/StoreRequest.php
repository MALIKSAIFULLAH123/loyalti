<?php

namespace MetaFox\Event\Http\Requests\v1\InviteCode;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
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
            'event_id' => ['required', 'numeric', 'exists:events,id'],
            'refresh'  => ['sometimes', 'integer', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data = Arr::add($data, 'refresh', 0);

        return $data;
    }
}
