<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class InformationField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Information');
    }
}
