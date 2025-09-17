<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class Typography extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_TYPOGRAPHY);
    }

    /**
     * @param string $text
     * @return $this
     */
    public function plainText(string $text): self
    {
        return $this->setAttribute('plainText', $text);
    }

    /**
     * One of primary, secondary, danger, info.
     * @param string $color
     * @return $this
     */
    public function color(string $color): self
    {
        return $this->setAttribute('color', $color);
    }

    /**
     * @param string $ele
     * @return $this
     */
    public function tagName(string $ele): self
    {
        return $this->setAttribute('tagName', $ele);
    }
}
