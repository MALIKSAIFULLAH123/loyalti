<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class DynamicTypography extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_DYNAMIC_TYPOGRAPHY);
    }

    public function relatedField(string $name): static
    {
        return $this->setAttribute('relatedField', $name);
    }

    public function data(array $data): static
    {
        return $this->setAttribute('data', $data);
    }
}
