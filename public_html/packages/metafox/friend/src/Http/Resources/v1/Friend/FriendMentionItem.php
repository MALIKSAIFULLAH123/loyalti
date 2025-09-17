<?php

namespace MetaFox\Friend\Http\Resources\v1\Friend;

use Illuminate\Support\Arr;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityItem;

class FriendMentionItem extends UserEntityItem
{
    public function toArray($request): array
    {
        $data = parent::toArray($request);

        $context = user();

        $extraInfo = app('events')->dispatch('friend.mention.extra_info', [$context, $this->resource], true);

        $data = array_merge($data, [
            'type'  => Arr::get($extraInfo, 'type'),
            'statistic' => [
                'total_people' => Arr::get($extraInfo, 'total_people'),
            ],
        ]);

        return $data;
    }
}
