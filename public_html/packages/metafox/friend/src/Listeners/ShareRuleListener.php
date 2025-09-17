<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Support\Friend;

class ShareRuleListener
{
    public function handle(?string $postType): ?array
    {
        if (Friend::SHARED_TYPE !== $postType) {
            return null;
        }

        return [
            'friends'        => ['required_if:post_type,' . Friend::SHARED_TYPE, 'array'],
            'friends.*'      => ['numeric', 'exists:user_entities,id'],
        ];
    }
}
