<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class VideoUploadField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('VideoUpload');
    }
}
