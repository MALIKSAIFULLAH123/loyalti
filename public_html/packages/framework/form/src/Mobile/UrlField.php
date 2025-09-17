<?php

namespace MetaFox\Form\Mobile;

class UrlField extends TextField
{
    public function initialize(): void
    {
        $this->setComponent('Text');
    }
}
