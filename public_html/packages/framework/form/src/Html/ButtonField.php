<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;

/**
 * Class ButtonField.
 */
class ButtonField extends AbstractField
{
    public function initialize(): void
    {
        $this->component('Button')
            ->color('primary')
            ->variant('outlined')
            ->sizeLarge()
            ->fullWidth(false);
    }

    /**
     * @param  string $color
     * @return $this
     */
    public function color(string $color): self
    {
        return $this->setAttribute('color', $color);
    }
}
