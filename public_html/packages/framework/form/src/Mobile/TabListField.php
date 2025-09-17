<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class TabListField extends AbstractField
{
    public const COMPONENT = 'TabList';

    public function initialize(): void
    {
        $this->setComponent(self::COMPONENT);
    }

    /**
     * @param  array<int, mixed> $options
     * @return $this
     */
    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }
}
