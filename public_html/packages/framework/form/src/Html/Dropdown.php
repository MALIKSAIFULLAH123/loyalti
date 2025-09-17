<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Dropdown.
 */
class Dropdown extends Choice
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::DROPDOWN)
            ->options([])
            ->fullWidth(true)
            ->variant('outlined');
    }

    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }
}
