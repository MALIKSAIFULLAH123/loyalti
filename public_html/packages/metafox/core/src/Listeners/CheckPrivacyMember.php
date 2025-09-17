<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Models\PrivacyMember;

/**
 * Class CheckPrivacyMember.
 * @description check whenever privacy_id has owned by specific user.
 */
class CheckPrivacyMember
{
    public function handle(int $userId, int $privacyId): bool
    {
        return PrivacyMember::where([
            'user_id'    => $userId,
            'privacy_id' => $privacyId,
        ])->exists();
    }
}
