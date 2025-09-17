<?php

namespace MetaFox\Activity\Traits;

use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

trait HasTaggedFriendTrait
{
    public function isEnableTagFriends(): bool
    {
        return app_active('metafox/friend') && Settings::get('activity.feed.enable_tag_friends', false) === true;
    }

    /**
     * @param array<string, mixed> $rules
     *
     * @return array<string, mixed>
     */
    public function applyTaggedFriendsRules(array $rules): array
    {
        if ($this->isEnableTagFriends()) {
            $rules['tagged_friends']   = ['sometimes', 'array'];
            $rules['tagged_friends.*'] = ['numeric', new ExistIfGreaterThanZero('exists:user_entities,id')];

            $rules['tagged_in_photo']             = ['sometimes', 'array'];
            $rules['tagged_in_photo.*']           = ['array'];
            $rules['tagged_in_photo.*.friend_id'] = ['numeric', new ExistIfGreaterThanZero('exists:user_entities,id')];
            $rules['tagged_in_photo.*.px']        = ['numeric'];
            $rules['tagged_in_photo.*.py']        = ['numeric'];
        }

        return $rules;
    }

    /**
     * @param array<string, mixed> $rules
     *
     * @return array<string, mixed>
     */
    public function applyTaggedFriendsRulesForEdit(array $rules): array
    {
        if ($this->isEnableTagFriends()) {
            $rules['tagged_friends']   = ['sometimes', 'array'];
            $rules['tagged_friends.*'] = ['nullable', 'integer', new ExistIfGreaterThanZero('exists:user_entities,id')];

            $rules['tagged_in_photo']             = ['sometimes', 'array'];
            $rules['tagged_in_photo.*']           = ['array'];
            $rules['tagged_in_photo.*.friend_id'] = ['numeric', new ExistIfGreaterThanZero('exists:user_entities,id')];
            $rules['tagged_in_photo.*.px']        = ['numeric'];
            $rules['tagged_in_photo.*.py']        = ['numeric'];
        }

        return $rules;
    }

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

        $exists = [
            'mention' => [],
            'tag'     => [],
        ];

        // Tagged in photo is high priority, override normal tag + mentions.
        if (array_key_exists('tagged_in_photo', $data)) {
            foreach ($data['tagged_in_photo'] as $tagData) {
                $friendId = Arr::get($tagData, 'friend_id');

                if (!$friendId) {
                    continue;
                }

                $result[] = $tagData;

                $exists['tag'][$friendId] = true;
            }
        }

        if (array_key_exists('tagged_friends', $data)) {
            foreach ($data['tagged_friends'] as $tagUserId) {
                if (Arr::exists($exists['tag'], $tagUserId)) {
                    continue;
                }

                $result[] = [
                    'friend_id' => $tagUserId,
                    'is_tag'    => 1,
                ];

                $exists['tag'][$tagUserId] = true;
            }
        }

        if (array_key_exists('content', $data) && is_string($data['content'])) {
            $mentions = app('events')->dispatch('user.get_mentions', [$data['content']]);

            if (is_array($mentions)) {
                $mentions = Arr::flatten($mentions);

                $mentions = Arr::where($mentions, function ($mention) {
                    return is_numeric($mention) && (int) $mention > 0;
                });

                $mentions = array_unique($mentions);

                foreach ($mentions as $mentionId) {
                    if (Arr::exists($exists['mention'], $mentionId)) {
                        continue;
                    }

                    $result[] = [
                        'friend_id'  => $mentionId,
                        'is_mention' => 1,
                        'content'    => $data['content'], // Support for notification.
                    ];

                    $exists['mention'][$mentionId] = true;
                }
            }
        }

        return $result;
    }
}
