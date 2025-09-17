<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class CopyText extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COPY_TEXT)
            ->marginNormal()
            ->sizeMedium()
            ->variant('outlined')
        ;
    }
}
