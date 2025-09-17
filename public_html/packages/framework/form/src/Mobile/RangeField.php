<?php
namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class RangeField extends AbstractField
{
    protected function prepare(): void
    {
        $this->setComponent(MetaFoxForm::COMPONENT_RANGE);
    }

    public function step(float $step): self
    {
        return $this->setAttribute('step', $step);
    }

    public function min(float $min, string $name, ?string $label = null): self
    {
        return $this->setAttribute('min', [
            'name' => $name,
            'label' => $label ?? __p('core::phrase.min'),
            'value' => $min,
        ]);
    }

    public function max(float $max, string $name, ?string $label = null): self
    {
        return $this->setAttribute('max', [
            'name' => $name,
            'label' => $label ?? __p('core::phrase.max'),
            'value' => $max,
        ]);
    }
}
