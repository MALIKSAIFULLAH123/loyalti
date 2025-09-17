<?php

namespace MetaFox\Core\Http\Requests\v1\Core;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Core\Repositories\AppSettingRepository;
use MetaFox\Platform\Rules\AllowInRule;

class SettingsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'type' => ['sometimes', 'string', new AllowInRule(AppSettingRepository::APP_SETTING_ALLOWABLE_TYPES)],
        ];
    }
}
