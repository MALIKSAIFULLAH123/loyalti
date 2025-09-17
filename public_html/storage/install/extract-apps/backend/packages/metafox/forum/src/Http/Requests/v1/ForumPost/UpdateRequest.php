<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumPost;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Rules\ResourceTextRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;

class UpdateRequest extends FormRequest
{
    use AttachmentRequestTrait;

    public function rules(): array
    {
        $rules = [
            'text' => ['sometimes', 'string', new ResourceTextRule(true)],
        ];

        $rules = $this->applyAttachmentRules($rules);

        $captchaRules = Captcha::ruleOf($this->getCaptchaRuleName());

        if (is_array($captchaRules)) {
            $rules['captcha'] = $captchaRules;
        }

        return $rules;
    }

    protected function getCaptchaRuleName(): string
    {
        return 'forum.' . ForumSupport::CAPTCHA_RULE_CREATE_POST;
    }
}
