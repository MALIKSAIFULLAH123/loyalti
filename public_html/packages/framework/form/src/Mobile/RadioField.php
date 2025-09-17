<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class RadioField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Radio');
    }
}
