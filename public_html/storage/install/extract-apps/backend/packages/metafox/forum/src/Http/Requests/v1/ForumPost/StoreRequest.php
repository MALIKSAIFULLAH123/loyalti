<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumPost;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\ResourceTextRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;

class StoreRequest extends FormRequest
{
    use AttachmentRequestTrait;

    public function rules(): array
    {
        $rules = [
            'thread_id' => ['required', 'numeric', 'exists:forum_threads,id'],
            'owner_id'  => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'text'      => ['required', 'string', new ResourceTextRule(true)],
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

        if (!array_key_exists('owner_id', $data)) {
            Arr::set($data, 'owner_id', 0);
        }
        if (!array_key_exists('attachments', $data)) {
            Arr::set($data, 'attachments', []);
        }

        return $data;
    }

    public function messages(): array
    {
        return [
            'thread_id.required' => __p('forum::validation.thread_id.required'),
            'thread_id.exists'   => __p('forum::validation.thread_id.exists'),
            'text.required'      => __p('forum::validation.text.required'),
            'text.string'        => __p('forum::validation.text.required'),
        ];
    }

    protected function getCaptchaRuleName(): string
    {
        return 'forum.' . ForumSupport::CAPTCHA_RULE_CREATE_POST;
    }
}
