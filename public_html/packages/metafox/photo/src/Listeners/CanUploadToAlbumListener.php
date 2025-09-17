<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Platform\Contracts\User;

class CanUploadToAlbumListener
{
    public function handle(User $context, ?User $owner, ?string $itemType = null): ?bool
    {
        if (null === $owner) {
            return false;
        }

        $can = policy_check(PhotoPolicy::class, 'uploadToAlbum', $context, $owner);

        if (null === $itemType) {
            if (!$can) {
                return null;
            }

            return true;
        }

        if ($itemType !== Photo::ENTITY_TYPE) {
            return null;
        }

        return $can;
    }
}
