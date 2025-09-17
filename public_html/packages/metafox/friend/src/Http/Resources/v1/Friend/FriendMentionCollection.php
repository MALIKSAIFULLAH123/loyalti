<?php

namespace MetaFox\Friend\Http\Resources\v1\Friend;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use MetaFox\User\Support\Facades\UserEntity;

class FriendMentionCollection extends ResourceCollection
{
    public $collects = FriendMentionItem::class;

    /**
     * TODO: Improve to move this handle to FriendMentionItem resource instead.
     */
    public function toArray($request)
    {
        $data = parent::toArray($request);

        if (!count($data)) {
            return $data;
        }

        $userId = $request->get('user_id');

        $ownerId = $request->get('owner_id');

        if (null === $ownerId) {
            return $data;
        }

        try {
            $owner = UserEntity::getById($ownerId)->detail;

            $user = null;

            if ($userId) {
                $user = UserEntity::getById($userId)->detail;
            }

            $temp = app('events')->dispatch('friend.mention.transform_data_after', [$owner, $data, $user], true);

            if (is_array($temp)) {
                $data = $temp;
            }
        } catch (\Throwable $exception) {
            return [];
        }

        return $data;
    }
}
