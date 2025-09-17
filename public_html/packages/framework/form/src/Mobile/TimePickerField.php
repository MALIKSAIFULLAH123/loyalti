<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class TimePickerField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Time');
    }
}
