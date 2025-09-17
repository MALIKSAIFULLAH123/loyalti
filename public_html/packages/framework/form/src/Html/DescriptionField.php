<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class DescriptionField.
 */
class DescriptionField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::DESCRIPTION);
    }

    /**
     * One of primary, secondary, danger, info.
     *
     * @param string $color
     *
     * @return $this
     */
    public function color(string $color): self
    {
        return $this->setAttribute('color', $color);
    }
}
