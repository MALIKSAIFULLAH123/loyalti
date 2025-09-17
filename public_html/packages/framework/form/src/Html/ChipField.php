<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class ChipField.
 * @method array  getOptions()
 * @method static setOptions(array $options)
 */
class ChipField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::CHIP);
    }

    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }
}
