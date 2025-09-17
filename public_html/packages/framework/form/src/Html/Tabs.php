<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Tabs.
 */
class Tabs extends Radio
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_TABS)
            ->fullWidth();
    }

    /**
     * @param array<array<string,mixed> $options
     *
     * @return $this
     */
    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }
}
