<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Form\AbstractField;
use MetaFox\Platform\MetaFoxFileType;

/**
 * Class SingleAudioField.
 */
class SingleAudioField extends File
{
    public function initialize(): void
    {
        $this->component('SingleAudioField')
            ->name('file')
            ->label(__p('core::web.music'))
            ->fileTypes('audio')
            ->accepts(file_type()->getMimeTypeFromType(MetaFoxFileType::AUDIO_TYPE, false))
            ->thumbnailSizes(ResizeImage::SIZE)
            ->maxUploadSize(file_type()->getFilesizePerType('audio'))
            ->uploadUrl('/file');
    }

    public function autoFillValueName(string $value): AbstractField
    {
        return $this->setAttribute('autoFillValueName', $value);
    }
}
