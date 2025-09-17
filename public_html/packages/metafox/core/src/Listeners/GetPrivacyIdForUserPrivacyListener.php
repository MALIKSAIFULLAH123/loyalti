<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Repositories\Contracts\PrivacyRepositoryInterface;

/**
 * Class GetPrivacyIdForUserPrivacyListener.
 *
 * @description used for user_privacy get privacy_id based on privacy 0, 1, 2 v.v...
 */
class GetPrivacyIdForUserPrivacyListener
{
    public function handle(int $ownerId, int $privacy): int
    {
        $repository = resolve(PrivacyRepositoryInterface::class);

        return $repository->getPrivacyIdForUserPrivacy($ownerId, $privacy);
    }
}
