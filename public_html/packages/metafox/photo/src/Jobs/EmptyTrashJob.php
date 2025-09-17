<?php

namespace MetaFox\Photo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\Platform\Jobs\AbstractJob;

class EmptyTrashJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function uniqueId(): string
    {
        return 'metafox_photo_' . __CLASS__;
    }

    public function handle(): void
    {
        $this->emptyPhotoTrashed();
        $this->emptyAlbumTrashed();
    }

    protected function emptyPhotoTrashed(): void
    {
        Photo::onlyTrashed()->forceDelete();
    }

    protected function emptyAlbumTrashed()
    {
        Album::onlyTrashed()->forceDelete();
    }
}
