<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Support\Friend;

class ShareFormListener
{
    public function handle(string $postType): ?array
    {
        if (Friend::SHARED_TYPE !== $postType) {
            return null;
        }

        return [
            'friends' => [
                'name'      => 'friends',
                'component' => 'Hidden',
            ],
        ];
    }
}
