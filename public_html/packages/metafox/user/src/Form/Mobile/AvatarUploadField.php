<?php

namespace MetaFox\User\Form\Mobile;

use MetaFox\Form\Mobile\FileField;
use MetaFox\Platform\MetaFoxFileType;

class AvatarUploadField extends FileField
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
