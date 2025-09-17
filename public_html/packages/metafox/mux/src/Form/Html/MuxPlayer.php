<?php

namespace MetaFox\Mux\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class MuxPlayer extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::MUX_PLAYER);
    }
}
