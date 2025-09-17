<?php

namespace MetaFox\Advertise\Http\Requests\v1\Advertise;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Rules\AllowInRule;

class UpdateTotalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', new AllowInRule([Support::TYPE_IMPRESSION, Support::TYPE_CLICK])],
        ];
    }
}
