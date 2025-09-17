<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class RadioGroupField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::RADIO_GROUP);
    }

    /**
     * @param  array<int, mixed> $data
     * @return $this
     */
    public function options(array $data): self
    {
        return $this->setAttribute('options', $data);
    }
}
