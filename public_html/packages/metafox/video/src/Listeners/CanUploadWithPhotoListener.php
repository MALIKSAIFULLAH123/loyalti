<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Policies\VideoPolicy;

class CanUploadWithPhotoListener
{
    public function handle(User $context, User $owner, string $entityType): ?bool
    {
        if (Video::ENTITY_TYPE != $entityType) {
            return null;
        }

        return policy_check(VideoPolicy::class, 'uploadWithPhoto', $context, $owner);
    }
}
