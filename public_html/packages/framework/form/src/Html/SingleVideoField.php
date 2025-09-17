<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;

/**
 * Class SingleVideoField.
 */
class SingleVideoField extends File
{
    public function initialize(): void
    {
        $this->component('SingleVideoFile')
            ->name('file')
            ->label(__p('video::phrase.video'))
            ->fileTypes('video')
            ->accepts('video/*')
            ->thumbnailSizes(ResizeImage::SIZE)
            ->maxUploadSize(file_type()->getFilesizePerType('video'))
            ->uploadUrl('/file')
            ->storageId('video');
    }
}
