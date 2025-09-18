<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Policies\VideoPolicy;

class CanUploadToAlbumListener
{
    public function handle(User $context, ?User $owner, ?string $itemType = null): ?bool
    {
        $can = policy_check(VideoPolicy::class, 'uploadToAlbum', $context, $owner);

        if (null === $itemType) {
            if (!$can) {
                return null;
            }

            return true;
        }

        if ($itemType !== Video::ENTITY_TYPE) {
            return null;
        }

        return $can;
    }
}
