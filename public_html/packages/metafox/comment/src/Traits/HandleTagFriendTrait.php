<?php

namespace MetaFox\Comment\Traits;

use Illuminate\Support\Arr;

trait HandleTagFriendTrait
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string|int, mixed>
     *                                  [
     *                                  1 => 1,
     *                                  2 => [
     *                                  'friend_id' => 2,
     *                                  'px' => 100,
     *                                  'py' => 200,
     *                                  ],
     *                                  3 => [
     *                                  'friend_id' => 2,
     *                                  'is_mention' => 1,
     *                                  'content' => 'user test ahihi',
     *                                  ],
     *                                  ]
     */
    public function handleTaggedFriend(array $data): array
    {
        $result = [];

        $text = Arr::get($data, 'text');

        if (!is_string($text)) {
            return $result;
        }

        $mentions = app('events')->dispatch('user.get_mentions', [$text]);

        if (!is_array($mentions)) {
            return $result;
        }

        foreach ($mentions as $mention) {
            if (!is_array($mention)) {
                continue;
            }

            foreach ($mention as $mentionId) {
                Arr::set($result, $mentionId, [
                    'friend_id'  => $mentionId,
                    'is_mention' => 1,
                    'content'    => $text, // Support for notification.
                ]);
            }
        }

        return $result;
    }
}
