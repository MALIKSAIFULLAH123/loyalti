<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class RadioGroupField.
 * @method bool  getDisableUncheck()
 * @method self  setDisableUncheck(bool $value)
 * @method array getOptions()
 * @method self  setOptions(array $options)
 */
class RadioGroupField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::RADIO_GROUP)
            ->options([])
            ->fullWidth(true);
    }

    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
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

    public function descriptionPlacement(string $placement): self
    {
        return $this->setAttribute('descriptionPlacement', $placement);
    }
}
