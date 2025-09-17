<?php

namespace MetaFox\Storage\Observers;

use MetaFox\Storage\Models\StorageFile;

/**
 * Class StorageFileObserver.
 * @ignore
 * @codeCoverageIgnore
 */
class StorageFileObserver
{
    public function forceDeleted(StorageFile $file): void
    {
        app('storage')->disk($file->target)->delete($file->path);
    }
}
