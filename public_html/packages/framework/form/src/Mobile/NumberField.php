<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class NumberField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Number');
    }
}
