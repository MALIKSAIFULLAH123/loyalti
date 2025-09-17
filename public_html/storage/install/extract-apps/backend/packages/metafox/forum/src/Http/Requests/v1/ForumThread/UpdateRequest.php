<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumThread;

use Illuminate\Support\Arr;
use MetaFox\Forum\Rules\WikiRule;

class UpdateRequest extends StoreRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        Arr::set($rules, 'is_closed', ['sometimes', 'boolean']);
        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = $this->validator->validated();

        if (!Arr::has($data, 'tags')) {
            Arr::set($data, 'tags', []);
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

        if (!array_key_exists('is_subscribed', $data)) {
            Arr::set($data, 'is_subscribed', 0);
        }

        if (!array_key_exists('is_closed', $data)) {
            Arr::set($data, 'is_closed', 0);
        }

        if (!array_key_exists('is_wiki', $data)) {
            Arr::set($data, 'is_wiki', 0);
        }

        if ($data['is_wiki']) {
            Arr::set($data, 'forum_id', 0);
        }

        if (Arr::has($data, 'title')) {
            Arr::set($data, 'title', trim(Arr::get($data, 'title')));
        }

        return $data;
    }

    protected function getWikiRule(): WikiRule
    {
        $rule = parent::getWikiRule();

        $id = $this->input('id');

        if (null !== $id) {
            $rule->setId($id);
        }

        return $rule;
    }
}
