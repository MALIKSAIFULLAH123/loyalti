<?php

namespace MetaFox\LiveStreaming\Support\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * @deprecated use \MetaFox\Mux\Form\Html\MuxPlayer instead
 */
class MuxPlayer extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::MUX_PLAYER);
    }
}
