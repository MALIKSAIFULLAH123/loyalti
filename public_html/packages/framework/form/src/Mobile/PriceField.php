<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class PriceField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::COMPONENT_PRICE)
            ->label(__p('core::phrase.price'))
            ->required()
            ->maxLength(12)
            ->variant('standard')
            ->sizeSmall();
    }
}
