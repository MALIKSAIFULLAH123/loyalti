<?php

namespace MetaFox\Friend\Support;

use MetaFox\Friend\Models\TagFriend;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\User\Models\UserEntity as UserEntityModel;

class LoadMissingAllTagFriends
{
    public function after(User $context, Reducer $reducer)
    {
        $userId   = $context->id;
        $contents = $reducer->entities()
            ->filter(fn ($x) => $x instanceof HasTaggedFriend)
            ->map(fn ($x) => [$x->ownerId(), $x->entityType(), $x->entityId()]);

        if ($contents->isEmpty()) {
            return;
        }

        $key = fn ($type, $id) => sprintf('friend:tagFriends(%s:%s)', $type, $id);

        /** @link \MetaFox\Friend\Repositories\Eloquent\TagFriendRepository::getAllTaggedFriends */
        /** @var $data */
        $data = $contents->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x[1], $x[2])] = new \Illuminate\Database\Eloquent\Collection();

            return $carry;
        }, []);

        if ($userId) {
            // check is tagged me
            $key2 = fn ($ownerId, $type, $id) => sprintf('friend:tag.of(user:%s,%s:%s)', $ownerId, $type, $id);
            $data = $contents->reduce(function ($carry, $x) use ($key2) {
                $carry[$key2($x[0], $x[1], $x[2])] = null;

                return $carry;
            }, $data);

            return TagFriend::query()
                ->where(function ($builder) use ($contents) {
                    $contents->each(fn ($x) => $builder->orWhere(fn ($q) => $q->where([
                        'owner_id'  => $x[0],
                        'item_type' => $x[1],
                        'item_id'   => $x[2],
                    ])));
                })
                ->get()
                ->reduce(function ($carry, $x) use ($key2, $reducer) {
                    $carry[$key2($x->owner_id, $x->item_type, $x->item_id)] = $x;
                    $reducer->addEntity($x);

                    return $carry;
                }, $data);
        }

        return UserEntityModel::query()
            ->selectRaw('user_entities.*, item_type, item_id')
            ->join('friend_tag_friends', 'user_entities.id', '=', 'friend_tag_friends.owner_id')
            ->where(function ($builder) use ($contents) {
                $contents->each(fn ($x) => $builder->orWhere(fn ($q) => $q->where([
                    'item_type' => $x[1],
                    'item_id'   => $x[2],
                ])));
            })->get()
            ->reduce(function ($carry, $x) use ($key, $reducer) {
                $c = $key($x->item_type, $x->item_id);

                if (array_key_exists($c, $carry)) {
                    $carry[$c]->add($x);
                }

                $reducer->addEntity($x);

                return $carry;
            }, $data);
    }
}
