<?php

namespace MetaFox\User\Listeners;

use Illuminate\Support\Arr;
use MetaFox\User\Jobs\CleanUserAvatarAfterDeletePhotoJob;
use MetaFox\User\Jobs\CleanUserCoverAfterDeletePhotoJob;
use MetaFox\Platform\Contracts\Content;

/**
 * Class PhotoAfterDeleteListener.
 * @ignore
 */
class PhotoAfterDeleteListener
{
    /**
     * @param  Content|null         $photo
     * @param  array<string, mixed> $extra
     * @return void
     */
    public function handle(?Content $photo = null, $extra = []): void
    {
        if (!$photo instanceof Content) {
            return;
        }

        $isCover  = Arr::get($extra, 'is_cover', false);
        $isAvatar = Arr::get($extra, 'is_avatar', false);

        if ($isAvatar) {
            CleanUserAvatarAfterDeletePhotoJob::dispatch($photo);
        }

        if ($isCover) {
            CleanUserCoverAfterDeletePhotoJob::dispatch($photo);
        }
    }
}
