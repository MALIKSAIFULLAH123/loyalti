<?php

namespace MetaFox\Video\Form\Mobile;

use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Form\Mobile\FileField as File;
use MetaFox\Platform\Facades\Settings;

/**
 * Class SingleVideoField.
 *
 * @driverType form-field-mobile
 * @driverName singleVideo
 */
class SingleVideoField extends File
{
    public const COMPONENT = 'SingleVideoFile';

    public function initialize(): void
    {
        $this->setComponent(self::COMPONENT)
            ->name('file')
            ->variant('standard')
            ->label(__p('video::phrase.video'))
            ->fileType('video')
            ->accept('video/*')
            ->maxUploadSize(Settings::get('storage.filesystems.max_upload_filesize', []))
            ->thumbnailSizes(ResizeImage::SIZE)
            ->uploadUrl('file')
            ->storageId('video');
    }

    public function fetchEndpoint(string $endpoint): self
    {
        return $this->setAttribute('fetchEndpoint', $endpoint);
    }
}
