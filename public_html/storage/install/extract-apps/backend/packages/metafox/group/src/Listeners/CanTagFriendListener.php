<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class CanTagFriendListener
{
    public function handle(?Model $model, ?User $user): bool
    {
        if (!$model instanceof Group) {
            return true;
        }

        if (!$user instanceof User) {
            return true;
        }

        if ($model->isPublicPrivacy()) {
            return true;
        }

        $member = $this->getMemberRepository()->getGroupMember($model->id, $user->entityId());

        return $member instanceof Member;
    }

    protected function getMemberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }
}
