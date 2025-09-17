<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class CheckboxField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Checkbox');
    }

    /**
     * Set checkedValue=true, and uncheckedValue=false.
     * @return $this
     */
    public function asBoolean(): self
    {
        return $this->setAttribute('checkedValue', true)
            ->setAttribute('uncheckedValue', false);
    }
}
