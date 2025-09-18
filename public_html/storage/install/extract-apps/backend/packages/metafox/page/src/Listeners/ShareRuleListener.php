<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Support\PageSupport;

class ShareRuleListener
{
    public function handle(?string $postType): ?array
    {
        if (PageSupport::SHARED_TYPE !== $postType) {
            return null;
        }

        return [
            'pages'          => ['required_if:post_type,' . PageSupport::SHARED_TYPE, 'array'],
            'pages.*'        => ['numeric', 'exists:user_entities,id'],
        ];
    }
}
