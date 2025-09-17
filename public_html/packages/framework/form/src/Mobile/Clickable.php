<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class Clickable extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_CLICKABLE);
    }

    /**
     * @param  array $params
     * @return $this
     */
    public function params(array $params): self
    {
        return $this->setAttribute('params', $params);
    }

    public function action(string $url): self
    {
        return $this->setAttribute('action', $url);
    }

    public function severity(string $style): self
    {
        return $this->setAttribute('severity', $style);
    }
}
