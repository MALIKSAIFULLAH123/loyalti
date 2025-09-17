<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class IntegerField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Integer');
    }
}
