<?php

namespace MetaFox\Captcha\Http\Requests\v1\Captcha;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Captcha\Support\Facades\Captcha;

class VerifyRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'action_name' => ['nullable', 'string'],
        ];

        $captchaRules = Captcha::ruleOf($this->get('action_name'));

        if (is_array($captchaRules)) {
            $rules['captcha'] = $captchaRules;
        }

        return $rules;
    }
}
