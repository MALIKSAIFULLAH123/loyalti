<?php
namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class SliderField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::COMPONENT_SLIDER);
    }

    public function step(float $step): self
    {
        return $this->setAttribute('step', $step);
    }

    public function min(float $min): self
    {
        return $this->setAttribute('min', $min);
    }

    public function max(float $max): self
    {
        return $this->setAttribute('max', $max);
    }

    public function valueNames(array $names): self
    {
        return $this->setAttribute('valueNames', $names);
    }
}
