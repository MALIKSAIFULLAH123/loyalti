<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class RangeSliderField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Range');
    }
}
