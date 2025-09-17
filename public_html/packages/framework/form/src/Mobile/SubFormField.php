<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class SubFormField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('SubForm');
    }
}
