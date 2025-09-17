<?php

namespace MetaFox\Friend\Repositories\Eloquent;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as MainCollection;
use MetaFox\Friend\Models\TagFriend;
use MetaFox\Friend\Repositories\FriendTagBlockedRepositoryInterface;
use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\HasTaggedFriendWithPosition;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\User\Models\UserEntity as UserEntityModel;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class TagFriendRepository.
 * @method TagFriend find($id, $columns = ['*'])
 * @method TagFriend getModel()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @ignore
 * @codeCoverageIgnore
 */
class TagFriendRepository extends AbstractRepository implements TagFriendRepositoryInterface
{
    use UserMorphTrait;

    public function model(): string
    {
        return TagFriend::class;
    }

    /**
     * @return FriendTagBlockedRepositoryInterface
     */
    private function friendTagBlockedRepository(): FriendTagBlockedRepositoryInterface
    {
        return resolve(FriendTagBlockedRepositoryInterface::class);
    }

    /**
     * @param HasTaggedFriend $item
     * @param User            $owner
     *
     * @return TagFriend|null
     */
    public function getTagFriend(HasTaggedFriend $item, User $owner): ?TagFriend
    {
        return LoadReduce::get(
            sprintf('friend:tag.of(user:%s,%s:%s)', $owner->entityId(), $item->entityType(), $item->entityId()),
            fn () => TagFriend::query()
                ->where([
                    'owner_id'   => $owner->entityId(),
                    'owner_type' => $owner->entityType(),
                    'item_id'    => $item->entityId(),
                    'item_type'  => $item->entityType(),
                ])
                ->first()
        );
    }

    public function getTagFriends(HasTaggedFriend $item, int $limit, array $excludedIds = []): Builder
    {
        $query = UserEntityModel::query()
            ->join('friend_tag_friends', function (JoinClause $join) use ($item) {
                $join->on('user_entities.id', '=', 'friend_tag_friends.owner_id');
                $join->where('item_id', $item->entityId());
                $join->where('item_type', $item->entityType());
            })
            ->where('is_mention', '=', 0);

        if (count($excludedIds)) {
            $query->whereNotIn('user_entities.id', $excludedIds);
        }

        return $query;
    }

    /**
     * @param Entity $item
     *
     * @return Collection
     * @link \MetaFox\Friend\Support\LoadMissingAllTagFriends
     */
    public function getAllTaggedFriends(Entity $item): Collection
    {
        if ($item->total_tag_friend < 1) {
            return new Collection();
        }

        return LoadReduce::get(
            sprintf('friend:tagFriends(%s:%s)', $item->entityType(), $item->entityId()),
            fn () => UserEntityModel::query()
                ->join('friend_tag_friends', function (JoinClause $join) use ($item) {
                    $join->on('user_entities.id', '=', 'friend_tag_friends.owner_id');
                    $join->where('item_id', $item->entityId());
                    $join->where('item_type', $item->entityType());
                })
                ->get(['user_entities.*'])
        );
    }

    /**
     * @param HasTaggedFriend $item
     * @param array|null      $friendIds
     *
     * @return Collection
     */
    public function getItemTagFriends(HasTaggedFriend $item, ?array $friendIds = null): Collection
    {
        $query = $this->getModel()->newQuery()
            ->with(['ownerEntity', 'item'])
            ->where('item_id', $item->entityId())
            ->where('item_type', $item->entityType());

        if (is_array($friendIds) && count($friendIds)) {
            $query->whereIn('owner_id', $friendIds);
        }

        return $query->get();
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function createTagFriend(User $context, HasTaggedFriend $item, array $tagFriends, bool $putToTagStream = true): bool
    {
        if (!count($tagFriends)) {
            return false;
        }

        $stored = [
            'mention' => [],
            'tag'     => [],
        ];

        $newTags = [];

        $taggedUserIds = collect($tagFriends)
            ->filter(fn ($tagFriend) => Arr::get($tagFriend, 'is_mention') != 1)
            ->unique('friend_id')
            ->pluck('friend_id')
            ->toArray();

        $userIds = collect($tagFriends)
            ->unique('friend_id')
            ->pluck('friend_id')
            ->toArray();

        $users = UserEntityModel::query()
            ->with(['detail'])
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        foreach ($tagFriends as $tagFriend) {
            $isMention = Arr::get($tagFriend, 'is_mention') == 1;

            $key = $isMention ? 'mention' : 'tag';

            $userId = Arr::get($tagFriend, 'friend_id');

            $storedIndex = sprintf('%s.%s', $key, $userId);

            if (!$userId) {
                continue;
            }

            if (Arr::has($stored, $storedIndex)) {
                continue;
            }

            $userEntity = Arr::get($users, $userId);

            if (!$userEntity instanceof UserEntityModel) {
                continue;
            }

            $attributes = [
                'owner_id'          => $userEntity->entityId(),
                'owner_type'        => $userEntity->entityType(),
                'item_id'           => $item->entityId(),
                'item_type'         => $item->entityType(),
                'user_id'           => $context->entityId(),
                'user_type'         => $context->entityType(),
                'px'                => Arr::get($tagFriend, 'px', 0),
                'py'                => Arr::get($tagFriend, 'py', 0),
                'is_mention'        => (int) $isMention,
                'content'           => Arr::get($tagFriend, 'content'),
                'send_notification' => $isMention ? !in_array($userEntity->entityId(), $taggedUserIds) : true,
            ];

            $model = TagFriend::query()->create($attributes);

            if (!$model) {
                continue;
            }

            Arr::set($stored, $storedIndex, true);

            if ($key == 'tag') {
                $newTags[] = $model;
            }
        }

        if (!$putToTagStream) {
            return true;
        }

        $storedUserIds = array_unique(array_merge(array_keys($stored['mention']), array_keys($stored['tag'])));

        if (!count($storedUserIds)) {
            return true;
        }

        $putToStreamUsers = $users->filter(function ($user) use ($storedUserIds) {
            return in_array($user->entityId(), $storedUserIds);
        });

        $this->putMultipleToTagStream($context, $item, $putToStreamUsers);

        app('events')->dispatch('friend.tag_friend_created', [$item, $newTags]);

        return true;
    }

    public function putMultipleToTagStream(User $context, HasTaggedFriend $item, MainCollection $users, array $attributes = []): void
    {
        if ($item->owner instanceof HasPrivacyMember) {
            return;
        }

        if (!$item->hasTagStream()) {
            return;
        }

        if (!$item instanceof ActivityFeedSource) {
            return;
        }

        $feedAction = $item->toActivityFeed();

        if (!$feedAction instanceof FeedAction) {
            return;
        }

        $attributes = array_merge($attributes, [
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
            'type_id'   => $feedAction->getTypeId(),
        ]);

        $users->each(function ($userEntity) use ($context, $feedAction, $item, $attributes) {
            $friend = $userEntity->detail;

            if (!$friend) {
                return;
            }
            app('events')->dispatch(
                'activity.feed_put_to_tag_stream',
                [
                    $context,
                    $friend,
                    $item->entityId(),
                    $item->entityType(),
                    $feedAction->getTypeId(),
                    $attributes,
                ],
                true
            );
        });
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function updateTagFriend(User $context, HasTaggedFriend $item, array $tagFriends): bool
    {
        $currentItems = $this->getModel()->newQuery()
            ->where([
                'item_id'   => $item->entityId(),
                'item_type' => $item->entityType(),
            ])
            ->get();

        if (!count($tagFriends) && !$currentItems->count()) {
            return false;
        }

        $userIds = array_unique(array_merge($currentItems->pluck('owner_id')->toArray(), collect($tagFriends)->pluck('friend_id')->toArray()));

        $userEntities = UserEntityModel::query()
            ->with(['detail'])
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        $this->handleNewTaggedItemsForUpdate($context, $item, $userEntities, $currentItems, $tagFriends);

        $this->handleUpdatedTaggedItemsForUpdate($item, $currentItems, $tagFriends);

        $this->handleRemovedTaggedItemsForUpdate($context, $item, $currentItems, $tagFriends);

        return true;
    }

    private function handleRemovedTaggedItemsForUpdate(User $context, HasTaggedFriend $item, Collection $currentItems, array $tagFriends): void
    {
        $condition = [
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
        ];

        /**
         * @var \Illuminate\Support\Collection $taggedItems
         */
        $taggedItems = collect($tagFriends)->filter(fn ($item) => Arr::get($item, 'is_mention') != 1)
            ->keyBy('friend_id');

        /**
         * @var \Illuminate\Support\Collection $taggedItems
         */
        $mentionedItems = collect($tagFriends)->filter(fn ($item) => Arr::get($item, 'is_mention') == 1)
            ->keyBy('friend_id');

        $currentTaggedItems = $currentItems->filter(fn ($item) => $item->is_mention == 0)
            ->keyBy('owner_id');

        $currenTaggedPositionItems = $currentItems->filter(fn ($item) => $item->px > 0 || $item->py > 0)
            ->keyBy('owner_id');

        $currentMentionedItems = $currentItems->filter(fn ($item) => $item->is_mention == 1)
            ->keyBy('owner_id');

        $removedTaggedItems = $currentTaggedItems->filter(function ($item) use ($taggedItems) {
            $item = $taggedItems->get($item->owner_id);

            if (null === $item) {
                return true;
            }

            return false;
        });

        $removedMentionedItems = $currentMentionedItems->filter(function ($item) use ($mentionedItems) {
            $item = $mentionedItems->get($item->owner_id);

            if (null === $item) {
                return true;
            }

            return false;
        });

        if ($removedTaggedItems->count()) {
            $removeIds = $removedTaggedItems->pluck('owner_id')->toArray();

            $taggedWithPosition = $currenTaggedPositionItems->pluck('owner_id')->toArray();

            $removeIds = array_diff($removeIds, $taggedWithPosition);

            if (count($removeIds)) {
                TagFriend::query()
                    ->where($condition)
                    ->whereIn('owner_id', $removeIds)
                    ->where('is_mention', 0)
                    ->get()
                    ->each(function ($item) {
                        $item->delete();
                    });

                app('events')->dispatch('friend.tag_friend_deleted', [$item, $removedTaggedItems->toArray()]);
            }
        }

        if ($removedMentionedItems->count()) {
            TagFriend::query()
                ->where($condition)
                ->whereIn('owner_id', $removedMentionedItems->pluck('owner_id')->toArray())
                ->where('is_mention', 1)
                ->get()
                ->each(function ($item) {
                    $item->delete();
                });
        }

        $removeTaggedStreamUserIds = array_diff($currentItems->pluck('owner_id')->toArray(), collect($tagFriends)->pluck('friend_id')->toArray());

        if (count($removeTaggedStreamUserIds)) {
            $this->handleDeleteFromTagStream($context, $removeTaggedStreamUserIds, $item);
        }
    }

    private function handleUpdatedTaggedItemsForUpdate(HasTaggedFriend $item, Collection $currentItems, array $tagFriends): void
    {
        if (!$item instanceof HasTaggedFriendWithPosition) {
            return;
        }

        /**
         * @var \Illuminate\Support\Collection $taggedItems
         */
        $taggedItems = collect($tagFriends)->filter(fn ($item) => Arr::get($item, 'is_mention') != 1)
            ->keyBy('friend_id');

        $currentTaggedItems = $currentItems->filter(fn ($item) => $item->is_mention == 0)
            ->keyBy('owner_id');

        $updatedTaggedItems = $currentTaggedItems->filter(function ($item) use ($taggedItems) {
            $item = $taggedItems->get($item->owner_id);

            if (null === $item) {
                return false;
            }

            return true;
        });

        if (!$updatedTaggedItems->count()) {
            return;
        }

        $updatedTaggedItems->each(function ($item) use ($taggedItems) {
            $px = Arr::get($taggedItems, sprintf('%s.px', $item->owner_id));
            $py = Arr::get($taggedItems, sprintf('%s.py', $item->owner_id));

            if (null === $px || null === $py) {
                return;
            }

            if ($px == $item->px && $py == $item->py) {
                return;
            }

            $item->fill([
                'px' => $px,
                'py' => $py,
            ]);

            $item->save();
        });
    }

    private function handleNewTaggedItemsForUpdate(User $context, HasTaggedFriend $item, Collection $userEntities, Collection $currentItems, array $tagFriends): void
    {
        $currentTaggedItems = $currentItems->filter(fn ($item) => $item->is_mention == 0)
            ->keyBy('owner_id');

        $currentMentionedItems = $currentItems->filter(fn ($item) => $item->is_mention == 1)
            ->keyBy('owner_id');

        $newTaggedItems = array_filter($tagFriends, function ($item) use ($currentTaggedItems) {
            $userId = Arr::get($item, 'friend_id');

            if (Arr::get($item, 'is_mention') == 1) {
                return false;
            }

            if (!$userId) {
                return false;
            }

            return !$currentTaggedItems->offsetExists($userId);
        });

        $newMentionItems = array_filter($tagFriends, function ($item) use ($currentMentionedItems) {
            $userId = Arr::get($item, 'friend_id');

            if (Arr::get($item, 'is_mention') != 1) {
                return false;
            }

            if (!$userId) {
                return false;
            }

            return !$currentMentionedItems->offsetExists($userId);
        });

        $newItems = array_merge($newTaggedItems, $newMentionItems);

        $this->createTagFriend($context, $item, $newItems, false);

        $putToStreamUserIds = array_diff(collect($tagFriends)->pluck('friend_id')->toArray(), $currentItems->pluck('owner_id')->toArray());

        if (count($putToStreamUserIds)) {
            $putToStreamUsers = $userEntities->filter(function ($userEntity) use ($putToStreamUserIds) {
                return in_array($userEntity->entityId(), $putToStreamUserIds);
            });

            $this->putMultipleToTagStream($context, $item, $putToStreamUsers);
        }

        app('events')->dispatch('friend.tag_friend_created', [$item, array_values($newTaggedItems)]);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function deleteTagFriend(int $id): bool
    {
        $taggedFriend = $this->find($id);

        $item = $taggedFriend->item;

        if (!$item instanceof HasTaggedFriend) {
            return false;
        }

        $this->friendTagBlockedRepository()->createTagBlocked($taggedFriend);

        $this->handleDeleteFromTagStream($taggedFriend->user, [$taggedFriend->owner_id], $item);

        app('events')->dispatch('friend.tag_friend_deleted', [$item, [$taggedFriend]]);

        return (bool) $taggedFriend->delete();
    }

    /**
     * @param HasTaggedFriend $item
     * @param array|null      $friendIds
     *
     * @return void
     */
    public function deleteItemTagFriends(HasTaggedFriend $item, ?array $friendIds = null): void
    {
        $tagFriends = $this->getItemTagFriends($item, $friendIds);

        app('events')->dispatch('friend.tag_friend_deleted', [$item, $tagFriends->toArray()]);

        foreach ($tagFriends as $tagFriend) {
            $tagFriend->delete();
        }
    }

    private function handleDeleteFromTagStream(User $context, array $friendIds, HasTaggedFriend $item): void
    {
        if (!$item->hasTagStream()) {
            return;
        }

        if (!$item instanceof ActivityFeedSource) {
            return;
        }

        $feedAction = $item->toActivityFeed();

        foreach ($friendIds as $friendId) {
            app('events')->dispatch(
                'activity.feed_delete_from_tag_stream',
                [
                    $context,
                    $friendId,
                    $item->entityId(),
                    $item->entityType(),
                    $feedAction->getTypeId(),
                ],
                true
            );
        }
    }

    public function getTaggedUserIdsByItem(Entity $item): array
    {
        $items = $this->getModel()->newQuery()
            ->join('user_entities', function (JoinClause $joinClause) {
                $joinClause->on('user_entities.id', '=', 'friend_tag_friends.owner_id');
            })
            ->where([
                'friend_tag_friends.item_id'   => $item->entityId(),
                'friend_tag_friends.item_type' => $item->entityType(),
            ])
            ->get();

        $result = [
            'mention' => [],
            'tag'     => [],
        ];

        if (!$items->count()) {
            return $result;
        }

        $items->each(function ($tag) use (&$result) {
            $params = [
                'friend_id'         => $tag->owner_id,
                'px'                => $tag->px,
                'py'                => $tag->py,
                'content'           => $tag->content,
                'send_notification' => $tag->send_notification,
                'is_mention'        => (int) $tag->is_mention,
            ];

            match ((bool) $tag->is_mention) {
                true    => Arr::set($result, sprintf('mention.%s', $tag->owner_id), $params),
                default => Arr::set($result, sprintf('tag.%s', $tag->owner_id), $params),
            };
        });

        return $result;
    }

    public function deleteOwnerData(User $user): void
    {
        $query = $this->getModel()->newModelQuery()
            ->where([
                'owner_id'   => $user->entityId(),
                'owner_type' => $user->entityType(),
            ]);

        foreach ($query->lazy() as $model) {
            if (!$model instanceof TagFriend) {
                continue;
            }

            app('events')->dispatch('friend.tag_friend_deleted', [$model->item, [$model]]);

            $model->delete();
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteMentionAndTagFriend(Entity $item, ?User $context): void
    {
        TagFriend::query()
            ->where([
                'owner_id'   => $context->entityId(),
                'owner_type' => $context->entityType(),
                'item_id'    => $item->entityId(),
                'item_type'  => $item->entityType(),
            ])->each(function (TagFriend $tagFriend) use ($item) {
                if (!$tagFriend->is_mention) {
                    $this->friendTagBlockedRepository()->createTagBlocked($tagFriend);

                    app('events')->dispatch('friend.tag_friend_deleted', [$item, [$tagFriend]]);
                }

                if ($item instanceof HasTaggedFriend) {
                    $this->handleDeleteFromTagStream($tagFriend->user, [$tagFriend->owner_id], $item);
                }

                $tagFriend->delete();
            });
    }

    public function getUsersForMention(Entity $item, array $ownerIds, string $ownerType): MainCollection
    {
        return $this->getModel()->newQuery()
            ->whereIn('owner_id', $ownerIds)
            ->where([
                'owner_type' => $ownerType,
                'item_id'    => $item->entityId(),
                'item_type'  => $item->entityType(),
                'is_mention' => 1,
            ])
            ->get()
            ->mapWithKeys(function (TagFriend $model) {
                return [$model->ownerId() => $model->owner];
            });
    }
}
