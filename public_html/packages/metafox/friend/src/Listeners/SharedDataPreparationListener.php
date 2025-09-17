<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Friend\Support\Friend;

class SharedDataPreparationListener
{
    public function handle(string $postType, array $data): ?array
    {
        if (Friend::SHARED_TYPE !== $postType) {
            return null;
        }

        $friends = Arr::get($data, 'friends', []);

        if (is_array($friends) && count($friends)) {
            Arr::set($data, 'owners', $friends);

            Arr::set($data, 'success_message', __p('friend::phrase.shared_to_your_friend_profile'));

            unset($data['friends']);
        }

        return $data;
    }
}
