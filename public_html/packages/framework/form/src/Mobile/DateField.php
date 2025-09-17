<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class DateField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Date');
    }

    public function minDate(string $min): self
    {
        return $this->setAttribute('minDate', $min);
    }

    public function maxDate(?string $max = null): self
    {
        if (is_string($max)) {
            $this->setAttribute('maxDate', $max);
        }

        return $this;
    }
}
