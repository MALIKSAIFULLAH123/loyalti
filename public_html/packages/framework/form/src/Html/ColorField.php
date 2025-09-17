<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * @deprecated 5.2.0
 */
class ColorField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_COLOR)
            ->label(__p('core::phrase.color'));
    }
}
