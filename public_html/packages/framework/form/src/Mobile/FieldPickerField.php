<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class FieldPickerField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('File');
    }
}
