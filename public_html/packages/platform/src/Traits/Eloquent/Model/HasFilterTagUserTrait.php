<?php

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserEntity;

trait HasFilterTagUserTrait
{
    public function transformTaggedFriends(User $context, User $user, ?User $owner, array $taggedFriends, ?string $content = null, ?HasTaggedFriend $item = null, bool $removeMentionOnContent = true): array
    {
        if (null === $owner) {
            return [];
        }

        $oldContent = $content;

        $existedTaggedUserIds = $existedMentionedUserIds = [];

        if ($item instanceof HasTaggedFriend) {
            $dataIds                 = app('events')->dispatch('friend.get_all_item_tagged_friend', [$item], true) ?? [];
            $existedTaggedUserIds    = Arr::get($dataIds, 'tag', []);
            $existedMentionedUserIds = Arr::get($dataIds, 'mention', []);
        }

        $extra = [
            'content' => $content,
        ];

        $newTaggedFriends = $currentTaggedFriends = [];

        foreach ($taggedFriends as $taggedFriend) {
            $friendId = Arr::get($taggedFriend, 'friend_id');

            $isMention = Arr::get($taggedFriend, 'is_mention') == 1;

            if (!$friendId) {
                continue;
            }

            $exists = false;

            if (!$isMention && Arr::exists($existedTaggedUserIds, $friendId)) {
                $currentTaggedFriends[] = $taggedFriend;
                $exists                 = true;
            }

            if ($isMention && Arr::exists($existedMentionedUserIds, $friendId)) {
                $currentTaggedFriends[] = $taggedFriend;
                $exists                 = true;
            }

            if ($exists) {
                continue;
            }

            $newTaggedFriends[] = $taggedFriend;
        }

        $newTaggedFriends = $this->handleTaggedUsers($context, $user, $owner, $newTaggedFriends);

        $newTaggedFriends = $this->handleMentionedUsers($context, $user, $owner, $newTaggedFriends, $extra['content'], $removeMentionOnContent);

        $extra['tagged_friends'] = array_merge($newTaggedFriends, $currentTaggedFriends);

        if ($oldContent != $extra['content']) {
            foreach ($extra['tagged_friends'] as $id => $friend) {
                if (!Arr::has($friend, 'content')) {
                    continue;
                }

                Arr::set($extra, sprintf('tagged_friends.%s.content', $id), $extra['content']);
            }
        }

        return $extra;
    }

    protected function handleMentionedUsers(User $context, User $user, User $owner, array $mentionedUsers, ?string &$content, bool $removeMentionOnContent = true): array
    {
        $mentionedItems = array_filter($mentionedUsers, function ($mention) {
            return 1 === Arr::get($mention, 'is_mention');
        });

        $mentionedItemIds = array_column($mentionedItems, 'friend_id');

        if (!count($mentionedItemIds)) {
            return $mentionedUsers;
        }

        $filteredItemIds = $this->filterMentionUsers($context, $user, $owner, $mentionedItemIds);

        $diff = array_diff($mentionedItemIds, $filteredItemIds);

        if (!count($diff)) {
            return $mentionedUsers;
        }

        $mentionedUsers = collect($mentionedUsers)->keyBy(fn ($item) => sprintf('%s_%s', Arr::get($item, 'friend_id'), Arr::get($item, 'is_mention', 0)));

        foreach ($diff as $id) {
            $key = sprintf('%s_1', $id);

            if (!$mentionedUsers->offsetExists($key)) {
                continue;
            }

            $mentionedUsers->forget($key);
        }

        $mentionedUsers = $mentionedUsers->values()->toArray();

        if ($removeMentionOnContent) {
            $content = $this->removeMentionsFromContent($content, $diff);
        }

        return $mentionedUsers;
    }

    protected function handleTaggedUsers(User $context, User $user, User $owner, array $taggedUsers): array
    {
        $taggedItems = array_filter($taggedUsers, function ($tag) {
            return null === Arr::get($tag, 'is_mention');
        });

        $taggedItemIds = array_column($taggedItems, 'friend_id');

        if (!count($taggedItemIds)) {
            return $taggedUsers;
        }

        $filteredItemIds = $this->filterTagUsers($context, $user, $owner, $taggedItemIds);

        $diff = array_diff($taggedItemIds, $filteredItemIds);

        if (!count($diff)) {
            return $taggedUsers;
        }

        $taggedUsers = collect($taggedUsers)->keyBy(fn ($item) => sprintf('%s_%s', Arr::get($item, 'friend_id'), Arr::get($item, 'is_mention', 0)));

        foreach ($diff as $id) {
            $key = sprintf('%s_0', $id);

            if (!$taggedUsers->offsetExists($key)) {
                continue;
            }

            $taggedUsers->forget($key);
        }

        $taggedUsers = $taggedUsers->values()->toArray();

        return $taggedUsers;
    }

    public function filterTagUsers(User $context, User $user, User $owner, array $taggedUserIds): array
    {
        if (!method_exists($owner, 'filterTagUsersByOwner')) {
            return $this->fallbackTagUsers($context, $user, $taggedUserIds);
        }

        return call_user_func([$owner, 'filterTagUsersByOwner'], $context, $user, $taggedUserIds);
    }

    public function filterMentionUsers(User $context, User $user, User $owner, array $mentionUserIds): array
    {
        if (!method_exists($owner, 'filterMentionUsersByOwner')) {
            return $this->fallbackMentionUsers($context, $user, $mentionUserIds);
        }

        return call_user_func([$owner, 'filterMentionUsersByOwner'], $context, $user, $mentionUserIds);
    }

    public function fallbackTagUsers(User $context, User $user, array $taggedUserIds): array
    {
        if (!count($taggedUserIds)) {
            return [];
        }

        $entities = UserEntity::query()
            ->whereIn('id', $taggedUserIds)
            ->where('entity_type', \MetaFox\User\Models\User::ENTITY_TYPE)
            ->get();

        if (!$entities->count()) {
            return [];
        }

        $userIds = $entities->pluck('id')->toArray();

        $friendIds = app('events')->dispatch('friend.filter_tag_friends_by_multiple_users', [$context, $user, $userIds], true);

        if (!is_array($friendIds)) {
            return [];
        }

        return $friendIds;
    }

    public function fallbackMentionUsers(User $context, User $user, array $mentionUserIds): array
    {
        if (!count($mentionUserIds)) {
            return [];
        }

        $entities = UserEntity::query()
            ->whereIn('id', $mentionUserIds)
            ->get();

        if (!$entities->count()) {
            return [];
        }

        $result = app('events')->dispatch('core.filter_mention_users', [$context, $user, $entities]);

        $result = Arr::flatten($result);

        $result = array_filter($result, function ($id) {
            return is_numeric($id) && (int) $id > 0;
        });

        if (!count($result)) {
            return [];
        }

        return array_unique($result);
    }

    public function removeMentionsFromContent(?string $content, array $removedIds): ?string
    {
        if (null === $content) {
            return null;
        }

        if (!count($removedIds)) {
            return $content;
        }

        return preg_replace_callback('/\[\w+=(\d+)\](.*?)\[\/\w+\]/', function ($match) use ($removedIds) {
            if (in_array($match[1], $removedIds)) {
                return $match[2];
            }

            return $match[0];
        }, $content);
    }
}
