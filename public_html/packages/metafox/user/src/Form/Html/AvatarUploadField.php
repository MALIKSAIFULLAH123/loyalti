<?php

namespace MetaFox\User\Form\Html;

use MetaFox\Core\Support\FileSystem\FileType;
use MetaFox\Form\Html\RawFileField;
use MetaFox\Platform\MetaFoxFileType;

class AvatarUploadField extends RawFileField
{
    public const COMPONENT = 'AvatarUpload';

    public function initialize(): void
    {
        $this->component(self::COMPONENT)
            ->accept(file_type()->getMimeTypeFromType(MetaFoxFileType::PHOTO_TYPE))
            ->multiple(false)
            ->marginNormal();
    }
}
