<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Radio.
 * @method bool  getDisableUncheck()
 * @method self  setDisableUncheck(bool $value)
 * @method array getOptions()
 * @method self  setOptions(array $options)
 */
class Radio extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::RADIO)
            ->options([])
            ->variant('outlined');
    }

    /**
     * @param array<array<string,mixed> $options
     *
     * @return $this
     */
    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }
}
