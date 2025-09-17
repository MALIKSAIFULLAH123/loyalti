<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\MetaFoxFileType;

/**
 * Class SinglePhotoField.
 */
class SinglePhotoField extends File
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::SINGLE_PHOTO)
            ->name('file')
            ->label(__p('photo::phrase.photo'))
            ->description(__p('photo::phrase.select_photo_field_description'))
            ->fileTypes('photo')
            ->thumbnailSizes(ResizeImage::SIZE)
            ->maxUploadSize(file_type()->getFilesizePerType('photo'))
            ->uploadUrl('/file')
            ->accepts(file_type()->getMimeTypeFromType(MetaFoxFileType::PHOTO_TYPE, false))
            ->setAttribute('isDropFile', true);

        $this->validation = [
            'type'       => 'object',
            'properties' => [
                'extension' => [
                    'type' => 'string',
                ],
            ],
        ];
    }

    /**
     * @return $this
     */
    public function returnBase64(bool $value = false): self
    {
        return $this->setAttribute('returnBase64', $value);
    }

    public function aspectRatio(string $ratio): self
    {
        return $this->setAttribute('aspectRatio', $ratio);
    }

    public function widthPhoto(string $width): self
    {
        return $this->setAttribute('widthPhoto', $width);
    }
}
