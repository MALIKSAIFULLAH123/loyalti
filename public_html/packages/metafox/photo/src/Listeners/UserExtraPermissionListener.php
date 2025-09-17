<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Platform\Contracts\HasCoverMorph;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\User\Models\User as ModelsUser;

class UserExtraPermissionListener
{
    /**
     * @param  User                 $context
     * @param  User|null            $user
     * @return array<string, mixed>
     */
    public function handle(User $context, ?User $user = null): array
    {
        /** @var ?PhotoPolicy $photoPolicy */
        $photoPolicy = PolicyGate::getPolicyFor(Photo::class);
        if (!$user instanceof User) {
            return [];
        }

        $coverPhoto = $this->getCover($user);
        $hasCover   = $coverPhoto instanceof Photo;

        return [
            'can_remove_profile_cover'  => $hasCover && $photoPolicy->removeProfileCoverOrAvatar($context, $coverPhoto),
            'can_remove_profile_avatar' => $photoPolicy->removeProfileCoverOrAvatar($context, $coverPhoto),
            'can_tag_friend_on_photo'   => $photoPolicy->tagFriend($context, $user),
        ];
    }

    protected function getCover(User $user): ?Model
    {
        if ($user instanceof ModelsUser) {
            $user = $user->profile;
        }

        if (!$user instanceof HasCoverMorph) {
            return null;
        }

        if (!$user->getCoverType()) {
            return null;
        }

        return $user->cover()->first();
    }
}
