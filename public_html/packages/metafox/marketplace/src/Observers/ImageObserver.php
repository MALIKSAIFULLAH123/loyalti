<?php

namespace MetaFox\Marketplace\Observers;

use MetaFox\Marketplace\Models\Image;

class ImageObserver
{
    public function deleted(Image $image): void
    {
        app('storage')->rollDown($image->image_file_id);
    }
}
