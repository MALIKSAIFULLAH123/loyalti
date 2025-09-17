<?php

namespace MetaFox\Form\Mobile;

class InputField extends TextField
{
    public function initialize(): void
    {
        $this->setComponent('Input');
    }
}
