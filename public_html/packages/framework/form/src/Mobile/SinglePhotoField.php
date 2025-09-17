<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Mobile;

use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Platform\Facades\Settings;

/**
 * Class SinglePhotoField.
 */
class SinglePhotoField extends FileField
{
    public function initialize(): void
    {
        parent::initialize();

        $this->name('file')
            ->fileType('photo')
            ->thumbnailSizes(ResizeImage::SIZE)
            ->maxUploadSize(Settings::get('storage.filesystems.max_upload_filesize', []))
            ->uploadUrl('/file');
    }
}
