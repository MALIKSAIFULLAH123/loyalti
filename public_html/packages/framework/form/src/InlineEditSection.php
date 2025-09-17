<?php

namespace MetaFox\Form;

class InlineEditSection extends Section
{
    public function initialize(): void
    {
        $this->setAttributes([
            'component' => 'InlineEditSection',
        ]);
    }
}
