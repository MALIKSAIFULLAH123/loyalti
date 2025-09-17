<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class SelectSubForm extends AbstractField
{
    public function initialize(): void
    {
        $this
            ->setComponent('SelectSubForm');
    }

    /**
     * @param array<array<string,mixed>> $data
     *
     * @return $this
     */
    public function options(array $data): self
    {
        return $this->setAttribute('options', $data);
    }
}
