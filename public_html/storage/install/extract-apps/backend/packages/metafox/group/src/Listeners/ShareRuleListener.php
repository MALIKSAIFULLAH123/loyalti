<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Support\Support;

class ShareRuleListener
{
    public function handle(?string $postType): ?array
    {
        if (Support::SHARED_TYPE !== $postType) {
            return null;
        }

        return [
            'groups'         => ['required_if:post_type,' . Support::SHARED_TYPE, 'array'],
            'groups.*'       => ['numeric', 'exists:user_entities,id'],
        ];
    }
}
