<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class ColorPicker extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_COLOR_PICKER)
            ->label(__p('core::phrase.color_picker'))
            ->fullWidth()
            ->marginNormal()
            ->sizeMedium();
    }
}
