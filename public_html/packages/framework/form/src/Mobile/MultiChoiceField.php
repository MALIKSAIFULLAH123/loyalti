<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class MultiChoiceField.
 */
class MultiChoiceField extends ChoiceField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_SELECT)
            ->multiple()
            ->fullWidth();
    }
}
