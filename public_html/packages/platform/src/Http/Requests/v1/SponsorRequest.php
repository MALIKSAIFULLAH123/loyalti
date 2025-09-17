<?php

namespace MetaFox\Platform\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Class SponsorRequest.
 */
class SponsorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'sponsor' => ['required', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        Arr::set($data, 'is_sponsor', (bool) $data['sponsor']);

        return $data;
    }
}
