<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class PriceField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_PRICE)
            ->label(__p('core::phrase.price'));
    }

    /**
     * @param array<array<string,mixed> $options
     *
     * @return $this
     */
    public function currencies(array $options): self
    {
        return $this->setAttribute('currencies', $options);
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
