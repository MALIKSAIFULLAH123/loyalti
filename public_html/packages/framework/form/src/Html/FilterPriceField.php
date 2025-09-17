<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants;

class FilterPriceField extends AbstractField
{
    public function initialize(): void
    {
        parent::initialize();

        $this->setComponent(Constants::COMPONENT_FILTER_PRICE)
            ->fromFieldName('price_from')
            ->toFieldName('price_to')
            ->fromFieldLabel(__p('core::phrase.min'))
            ->toFieldLabel(__p('core::phrase.max'))
            ->separatorLabel(__p('core::web.to'))
            ->submitFieldLabel(__p('core::phrase.submit'))
            ->setAttribute('minNumber', 0);
    }

    public function submitFieldLabel(string $label): self
    {
        return $this->setAttribute('submitFieldLabel', $label);
    }

    public function separatorLabel(string $label): self
    {
        return $this->setAttribute('separatorLabel', $label);
    }

    public function fromFieldLabel(string $name): self
    {
        return $this->setAttribute('fromFieldLabel', $name);
    }

    public function toFieldLabel(string $name): self
    {
        return $this->setAttribute('toFieldLabel', $name);
    }

    public function fromFieldName(string $name): self
    {
        return $this->setAttribute('fromFieldName', $name);
    }

    public function toFieldName(string $name): self
    {
        return $this->setAttribute('toFieldName', $name);
    }
}
