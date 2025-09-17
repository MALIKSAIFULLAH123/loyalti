<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;

class TextResize extends AbstractField
{
    public const COMPONENT = 'TextResize';

    public function initialize(): void
    {
        $this->component(self::COMPONENT)
            ->variant('outlined')
            ->fullWidth()
            ->maxLength(255);
    }

    /**
     * @param string $flag
     * @return self
     * $flag: auto | on | off
     */
    public function showTooltip(string $flag = 'auto'): self
    {
        return $this->setAttribute('showTooltip', $flag);
    }

    public function max(int $max): self
    {
        return $this->setAttribute('max', $max);
    }

    public function min(int $min): self
    {
        return $this->setAttribute('min', $min);
    }
}
