<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class MultiChoiceField.
 */
class MultiChoiceField extends Choice
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_SELECT)
            ->multiple()
            ->fullWidth();
    }
}
