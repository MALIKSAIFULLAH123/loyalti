<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants;

class FilterPriceField extends AbstractField
{
    public function initialize(): void
    {
        parent::initialize();

        $this->setComponent(Constants::COMPONENT_FILTER_PRICE)
            ->label(__p('core::phrase.price'))
            ->fromFieldName('price_from')
            ->toFieldName('price_to')
            ->fromFieldLabel(__p('core::phrase.min'))
            ->toFieldLabel(__p('core::phrase.max'))
            ->fromFieldPlaceholder(__p('core::phrase.min'))
            ->toFieldPlaceholder(__p('core::phrase.max'))
            ->separatorLabel(__p('core::web.to'))
            ->submitFieldLabel(__p('core::phrase.submit'))
            ->setAttribute('minNumber', 0)
            ->variant('standard');
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

    public function fromFieldPlaceholder(string $name): self
    {
        return $this->setAttribute('fromFieldPlaceholder', $name);
    }

    public function toFieldPlaceholder(string $name): self
    {
        return $this->setAttribute('toFieldPlaceholder', $name);
    }

    public function fromFieldName(string $name): self
    {
        return $this->setAttribute('fromFieldName', $name);
    }

    public function toFieldName(string $name): self
    {
        return $this->setAttribute('toFieldName', $name);
    }

    public function enableSearch(bool $flag = true): static
    {
        return $this->setAttribute('enable_search', $flag);
    }
}
