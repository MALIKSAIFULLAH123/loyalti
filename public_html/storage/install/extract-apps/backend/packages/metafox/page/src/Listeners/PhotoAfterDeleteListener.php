<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Page\Jobs\CleanPageAvatarAfterDeletePhotoJob;
use MetaFox\Page\Jobs\CleanPageCoverAfterDeletePhotoJob;
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

        if ($isCover) {
            CleanPageCoverAfterDeletePhotoJob::dispatch($photo);
        }

        if ($isAvatar) {
            CleanPageAvatarAfterDeletePhotoJob::dispatch($photo);
        }
    }
}
