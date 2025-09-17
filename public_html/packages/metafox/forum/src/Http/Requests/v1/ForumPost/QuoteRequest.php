<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumPost;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Rules\ResourceTextRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;

class QuoteRequest extends FormRequest
{
    use AttachmentRequestTrait;

    public function rules(): array
    {
        $rules = [
            'quote_id' => ['required', 'numeric', 'exists:forum_posts,id'],
            'text'     => ['required', 'string', new ResourceTextRule()],
        ];

        $rules = $this->applyAttachmentRules($rules);

        $captchaRules = Captcha::ruleOf($this->getCaptchaRuleName());

        if (is_array($captchaRules)) {
            $rules['captcha'] = $captchaRules;
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!array_key_exists('attachments', $data)) {
            Arr::set($data, 'attachments', []);
        }

        return $data;
    }

    public function messages(): array
    {
        return [
            'text.required' => __p('forum::validation.text.required'),
            'text.string'   => __p('forum::validation.text.required'),
        ];
    }

    protected function getCaptchaRuleName(): string
    {
        return 'forum.' . ForumSupport::CAPTCHA_RULE_CREATE_POST;
    }
}
