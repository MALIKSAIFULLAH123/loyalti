<?php

namespace MetaFox\ChatPlus\Http\Resources\v1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\ChatPlus\Support\Traits\ChatplusTrait;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\UserBlocked;
use MetaFox\User\Traits\UserHasValuePermissionTrait;

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

/**
 * Class UserItem.
 * @property User $resource
 */
class UserItem extends JsonResource
{
    use ChatplusTrait;
    use UserHasValuePermissionTrait;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->roles = $resource?->roles;
    }

    /**
     * @param Request $request
     *
     * @return array<mixed>
     */
    public function toArray($request)
    {
        $profile = $this->resource->profile;
        $friends = app('events')->dispatch('friend.friend_ids', [$this->resource->entityId()], true);
        $role    = $this->getRole();

        return [
            'id'        => (string) $this->resource->id,
            'email'     => $this->resource->email ?? $this->resource->user_name . '@noemail.chatplus.com',
            'name'      => $this->resource->full_name,
            'username'  => $this->resource->user_name,
            'fname'     => $this->resource->full_name,
            'avatar'    => $this->getChatplusAvatar($profile, '200x200'),
            'verified'  => $this->resource->hasVerified(),
            'active'    => true,
            'approved'  => $this->resource->approve_status == MetaFoxConstant::STATUS_APPROVED,
            'roles'     => $role ? $this->getChatplusRole($role->entityId()) : ['user'],
            'blocked'   => $this->getAllBlockUsers($this->resource),
            'friends'   => $friends,
            'bio'       => $profile?->bio,
            'language'  => $profile?->language_id,
            'invisible' => $this->resource->is_invisible,
        ];
    }
}
