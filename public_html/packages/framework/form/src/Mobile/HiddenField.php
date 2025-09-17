<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class HiddenField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Hidden');
    }
}
