<?php

namespace MetaFox\Form\Html\Traits;

trait CssTrait
{
    public function color(string $color): self
    {
        return $this->setAttribute('color', $color);
    }
}
