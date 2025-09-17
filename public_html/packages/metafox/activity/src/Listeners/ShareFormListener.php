<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Support\Support;
use MetaFox\Platform\MetaFoxPrivacy;

class ShareFormListener
{
    public function handle(string $postType): ?array
    {
        if (Support::SHARED_TYPE !== $postType) {
            return null;
        }

        return [
            'privacy' => [
                'name'           => 'privacy',
                'component'      => 'Privacy',
                'value'          => 0,
                'options'        => [
                    [
                        'phrase' => 'Everyone',
                        'label'  => 'Everyone',
                        'value'  => MetaFoxPrivacy::EVERYONE,
                    ],
                    [
                        'phrase' => 'Friends',
                        'label'  => 'Friends',
                        'value'  => MetaFoxPrivacy::FRIENDS,
                    ],
                    [
                        'phrase' => 'Friends of Friends',
                        'label'  => 'Friends of Friends',
                        'value'  => MetaFoxPrivacy::FRIENDS_OF_FRIENDS,
                    ],
                    [
                        'phrase' => 'Only Me',
                        'label'  => 'Only Me',
                        'value'  => MetaFoxPrivacy::ONLY_ME,
                    ],
                    [
                        'phrase' => 'Everyone',
                        'label'  => 'Everyone',
                        'value'  => MetaFoxPrivacy::CUSTOM,
                    ],
                ],
                'returnKeyType' => 'next',
                'label'         => 'Privacy',
                'multiple'      => false,
            ],
        ];
    }
}
