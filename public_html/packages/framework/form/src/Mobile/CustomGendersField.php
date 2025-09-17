<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class CustomGendersField extends ChoiceField
{
    public function initialize(): void
    {
        parent::initialize();

        $this->setComponent('CustomGenders');
    }
}
