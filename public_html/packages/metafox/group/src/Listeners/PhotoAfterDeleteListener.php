<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Group\Jobs\CleanGroupCoverAfterDeletePhotoJob;
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

        if ($isCover) {
            CleanGroupCoverAfterDeletePhotoJob::dispatch($photo);
        }
    }
}
