<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumThread;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Forum\Rules\CheckModeratorPermissionRule;
use MetaFox\Forum\Rules\IntegratedItemRule;
use MetaFox\Forum\Rules\RequiredForumRule;
use MetaFox\Forum\Rules\WikiRule;
use MetaFox\Forum\Support\Facades\ForumThread;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\ResourceTextRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Platform\Traits\Http\Request\TagRequestTrait;

/**
 * StoreRequest.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StoreRequest extends FormRequest
{
    use AttachmentRequestTrait;
    use TagRequestTrait;

    /**
     * rules.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        $maxTitleLength = Settings::get('forum.maximum_name_length', ForumThread::getDefaultMaximumTitleLength());
        $minTitleLength = Settings::get('forum.minimum_name_length', ForumThread::getDefaultMinimumTitleLength());
        $wikiRule       = $this->getWikiRule();
        $context        = user();

        $rules = [
            'forum_id'        => [new RequiredForumRule(), new CheckModeratorPermissionRule()],
            'title'           => ['required', 'string', 'between: ' . $minTitleLength . ',' . $maxTitleLength],
            'text'            => ['required', 'string', new ResourceTextRule(true)],
            'tags'            => ['sometimes', 'array'],
            'tags.*'          => ['string'],
            'is_subscribed'   => ['sometimes', new AllowInRule([0, 1])],
            'is_wiki'         => ['sometimes', $wikiRule],
            'item_type'       => ['nullable', 'string'],
            'item_id'         => ['nullable', 'integer'],
            'integrated_item' => ['sometimes', 'nullable', new IntegratedItemRule($context)],
            'owner_id'        => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
        ];

        $rules            = $this->applyAttachmentRules($rules);
        $rules            = $this->applyTagRules($rules);
        $rules['captcha'] = Captcha::ruleOf('forum.' . ForumSupport::CAPTCHA_RULE_CREATE_THREAD);

        return $rules;
    }

    protected function getWikiRule(): WikiRule
    {
        $context = user();

        return new WikiRule($context);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!Arr::has($data, 'tags')) {
            Arr::set($data, 'tags', []);
        }

        if (!array_key_exists('forum_id', $data)) {
            Arr::set($data, 'forum_id', 0);
        }

        if (!array_key_exists('owner_id', $data)) {
            Arr::set($data, 'owner_id', 0);
        }

        if (!array_key_exists('item_id', $data)) {
            Arr::set($data, 'item_id', 0);
        }

        if (!array_key_exists('is_subscribed', $data)) {
            Arr::set($data, 'is_subscribed', 0);
        }

        if (!array_key_exists('is_closed', $data)) {
            Arr::set($data, 'is_closed', 0);
        }

        if (!array_key_exists('is_wiki', $data)) {
            Arr::set($data, 'is_wiki', 0);
        }

        if (!array_key_exists('item_id', $data)) {
            Arr::set($data, 'item_id', 0);
        }

        if (!array_key_exists('item_type', $data)) {
            Arr::set($data, 'item_type', null);
        }

        if (!array_key_exists('integrated_item', $data) || null === $data['integrated_item']) {
            Arr::set($data, 'integrated_item', []);
        }

        if (!array_key_exists('attachments', $data)) {
            Arr::set($data, 'attachments', []);
        }

        if ($data['is_wiki']) {
            $data['forum_id'] = 0;
        }

        return $data;
    }

    /**
     * messages.
     *
     * @return array<mixed>
     */
    public function messages(): array
    {
        $maxTitleLength = Settings::get('forum.maximum_name_length', ForumThread::getDefaultMaximumTitleLength());

        $minTitleLength = Settings::get('forum.minimum_name_length', ForumThread::getDefaultMinimumTitleLength());

        return [
            'title.required' => __p('core::validation.name.required'),
            'title.string'   => __p('core::validation.name.required'),
            'title.between'  => __p('core::validation.name.length_between', [
                'min' => $minTitleLength,
                'max' => $maxTitleLength,
            ]),
            'text.required' => __p('forum::validation.text.required'),
            'text.string'   => __p('forum::validation.text.required'),
        ];
    }
}
