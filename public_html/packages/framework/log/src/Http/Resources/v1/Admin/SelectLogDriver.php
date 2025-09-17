<?php

namespace MetaFox\Log\Http\Resources\v1\Admin;

use MetaFox\Form\AbstractForm;

class SelectLogDriver extends AbstractForm
{
    protected function initialize(): void
    {
        $this->addDefaultFooter();
    }
}
